<?php

require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata');
Zend_Loader::loadClass('Zend_Gdata_AuthSub');
Zend_Loader::loadClass('Zend_Http_Client');
Zend_Loader::loadClass('Zend_Uri_Http');
Zend_Loader::loadClass('Zend_Gdata_Query');
Zend_Loader::loadClass('Zend_Gdata_Feed');

define('GOOGLE_CONTACTS_URL', 'https://www.google.com/m8/feeds/contacts/default/full');
define('GOOGLE_GROUP_CONTACTS_URL', 'https://www.google.com/m8/feeds/contacts/default/base/');
define('GOOGLE_GROUPS_URL', 'https://www.google.com/m8/feeds/groups/default/full');

define('GOOGLE_CONTACTS_MAX_RESULTS', 1000);

/**
 * Documentatie voor google GData protocol:
 * algemeen, interactie: http://code.google.com/apis/contacts/docs/3.0/developers_guide_protocol.html
 * gd-namespace: http://code.google.com/apis/gdata/docs/2.0/elements.html
 * gContact-namespace: http://code.google.com/apis/contacts/docs/3.0/reference.html
 */
class GoogleSync {

	private $gdata = null;
	private $groupname = 'C.S.R.-import';
	//feed contents
	private $groupFeed = null; // Zend GData feed object for groups
	private $groupid = null;  // google-id van de groep waar alles in terecht moet komen...
	private $contactFeed = null; // Zend GData feed object for contacts
	private $contactData = null; // an array containing array's with some data for each contact.
	//sigleton pattern
	private static $instance;

	public static function instance() {
		if (!isset(self::$instance)) {
			self::$instance = new GoogleSync();
		}
		return self::$instance;
	}

	private function __construct() {
		if (!isset($_SESSION['google_token'])) {
			throw new Exception('Authsub token not available');
		}

		if (LidInstellingen::get('googleContacts', 'groepnaam') != '') {
			$this->groupname = trim(LidInstellingen::get('googleContacts', 'groepnaam'));
			if ($this->groupname == '') {
				$this->groupname = 'C.S.R.-import';
			}
		}
		$client = Zend_Gdata_AuthSub::getHttpClient($_SESSION['google_token']);

		//$client->setHeaders('If-Match: *'); //delete or update only if not changed since it was last read.
		$this->gdata = new Zend_Gdata($client);
		$this->gdata->setMajorProtocolVersion(3);

		//first load group feed, find or create the groupname from the user settings.
		$this->loadGroupFeed();
		$this->groupid = $this->getGroupId();

		//then load the contacts for this group.
		$this->loadContactsForGroup($this->groupid);

		//copy setting from settings manager.
		$this->extendedExport = LidInstellingen::get('googleContacts', 'extended') == 'ja';
	}

	/**
	 * Load all contactgroups.
	 */
	private function loadGroupFeed() {
		$query = new Zend_Gdata_Query(GOOGLE_GROUPS_URL);
		$this->groupFeed = $this->gdata->getFeed($query);
	}

	/**
	 * Load all google contacts.
	 */
	private function loadContactFeed() {
		$url = GOOGLE_CONTACTS_URL . '?max-results=' . GOOGLE_CONTACTS_MAX_RESULTS;

		$query = new Zend_Gdata_Query($url);
		$this->contactFeed = $this->gdata->getFeed($query);
	}

	/**
	 * Load contacts from certain contact group.
	 */
	private function loadContactsForGroup($groupId) {
		$url = GOOGLE_CONTACTS_URL . '?max-results=' . GOOGLE_CONTACTS_MAX_RESULTS;
		$url .= '&group=' . urlencode($groupId);

		$query = new Zend_Gdata_Query($url);
		$this->contactFeed = $this->gdata->getFeed($query);
	}

	/**
	 * Trek naam googleId en wat andere relevante meuk uit de feed-objecten
	 * en stop dat in een array.
	 */
	public function getGoogleContacts() {
		if ($this->contactData == null) {
			$this->contactData = array();
			foreach ($this->contactFeed as $contact) {

				//digg up uid.
				$uid = null;
				foreach ($contact->getExtensionElements() as $ext) {
					if ($ext->rootElement == 'userDefinedField') {
						$attr = $ext->getExtensionAttributes();
						if (isset($attr['key']['value']) && $attr['key']['value'] == 'csruid') {
							$uid = $attr['value']['value'];
						}
					}
				}

				$etag = substr($contact->getEtag(), 1, strlen($contact->getEtag()) - 2);
				$this->contactData[] = array(
					'name'	 => (string) $contact->title,
					'etag'	 => $etag,
					'id'	 => (string) $contact->id,
					'self'	 => $contact->getLink('self')->href,
					'csruid' => $uid
						//, 'xml'=>htmlspecialchars(str_replace('><', ">\n<", $contact->getXML()))
				);
			}
		}

		return $this->contactData;
	}

	/**
	 * Plaats een foto voor een google contact.
	 *
	 * @param $photolink link uit een google-entry waar de foto naartoe moet.
	 * @param $filename bestandsnaam van de foto die moet worden opgestuurd.
	 */
	private function putPhoto($photolink, $filename) {
		$this->gdata->put(file_get_contents($filename), $photolink, null, 'image/*');
	}

	/**
	 * Check of een Lid al voorkomt in de lijst met contacten zoals ingeladen van google.
	 *
	 * @param $profiel Lid waarvan de aanwezigheid gechecked moet worden.
	 *
	 * @return string met het google-id in het geval van voorkomen, anders null.
	 */
	public function existsInGoogleContacts(Profiel $profiel) {
		$name = strtolower($profiel->getNaam());
		foreach ($this->getGoogleContacts() as $contact) {

			if (
					$contact['csruid'] == $profiel->uid OR
					strtolower($contact['name']) == $name OR
					str_replace(' ', '', strtolower($contact['name'])) == str_replace(' ', '', $name)
			) {
				return $contact['id'];
			}
		}
		return null;
	}

	/**
	 * return the etag for any matching contact in this->contactFeed.
	 */
	public function getEtag($googleid) {
		foreach ($this->getGoogleContacts() as $contact) {
			if (strtolower($contact['id']) == $googleid) {
				return $contact['etag'];
			}
		}
		return null;
	}

	public function getLinkSelf($googleid) {
		foreach ($this->getGoogleContacts() as $contact) {
			if (strtolower($contact['id']) == $googleid) {
				return $contact['self'];
			}
		}
		return null;
	}

	/**
	 * Get array with group[name] => id
	 */
	function getGroups() {
		$return = array();
		foreach ($this->groupFeed as $group) {
			$title = (string) $group->title;

			if (substr($title, 0, 13) == 'System Group:') {
				$title = substr($title, 14);
			}
			//viesss, check of er een SystemGroup-tag bestaat, zo ja, het systemgroupid
			//opslaan in de array.
			//Dit ID hebben we nodig om onafhankelijk van de ingestelde taal @google de system
			//group 'My Contacts' te kunnen gebruiken
			$systemgroup = null;
			if (is_array($group->getExtensionElements())) {
				$extensions = $group->getExtensionElements();
				if (count($extensions) > 0) {
					$systag = $extensions[0];
					if ($systag->rootElement) {
						$sysattr = $systag->getExtensionAttributes();
						if (isset($sysattr['id'])) {
							$systemgroup = $sysattr['id']['value'];
						}
					}
				}
			}

			$return[] = array(
				'id'			 => $group->id->getText(),
				'name'			 => $title,
				'systemgroup'	 => $systemgroup
			);
		}
		return $return;
	}

	/**
	 * id van de systemgroup aan de hand van de system-group-id ophalen
	 * 
	 * http://code.google.com/apis/contacts/docs/2.0/reference.html#GroupElements
	 */
	private function getSystemGroupId($name) {
		//kijken of we al een grop hebben met de naam
		foreach ($this->getGroups() as $group) {
			if ($group['systemgroup'] == $name) {
				return $group['id'];
			}
		}
		return null;
	}

	/**
	 * Get the groupid for the group $this->groupname, or create and return groupname.
	 *
	 * @return string met het google group-id.
	 */
	private function getGroupId($groupname = null) {
		if ($groupname == null) {
			$groupname = $this->groupname;
		}
		//kijken of we al een grop hebben met de naam
		foreach ($this->getGroups() as $group) {
			if ($group['name'] == $groupname) {
				return $group['id'];
			}
		}

		//zo niet, dan maken deze groep nieuw aan.
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$entry = $doc->createElement('atom:entry');
		$entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:atom', 'http://www.w3.org/2005/Atom');
		$entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gd', 'http://schemas.google.com/g/2005');
		$doc->appendChild($entry);

		$title = $doc->createElement('atom:title', $groupname);
		$title->setAttribute('type', 'text');
		$entry->appendChild($title);

		$response = $this->gdata->insertEntry($doc->saveXML(), GOOGLE_GROUPS_URL);

		//herlaad groupFeed om de nieuw gemaakte daar ook in te hebben.
		$this->loadGroupFeed();

		return (string) $response->id;
	}

	/**
	 * Een hele serie leden syncen naar google contacts.
	 *
	 * @param $leden array van uid's of Lid-objecten die moeten worden gesynced
	 *
	 * @return string met foutmeldingen en de namen van de gesyncte leden.
	 */
	public function syncLidBatch($leden) {
		//kan veel tijd kosten, dus time_limit naar 0 zodat het oneindig door kan gaan.
		set_time_limit(0);

		$profielBatch = array();
		foreach ($leden as $profiel) {
			if ($profiel instanceof Profiel) {
				$profielBatch[] = $profiel;
			} else {
				try {
					$profielBatch[] = ProfielModel::get($profiel);
				} catch (Exception $e) {
					// omit faulty/non-existant uid's
				}
			}
		}
		$message = '';

		//dit zou netjes kunnen door één xml-bestand te maken en dat één
		//keer te posten, maar daar heb ik nu even geen zin in.
		//btw: google heeft een batch-limit van 100 acties.
		//zie ook: http://code.google.com/apis/gdata/docs/batch.html
		foreach ($profielBatch as $profiel) {
			$message.=$this->syncLid($profiel) . ', ';
		}
		return $message;
	}

	/**
	 * Een enkel lid syncen naar Google contacts.
	 *
	 * @param $profiel uid of Lid-object
	 *
	 * @return string met foutmelding of naam van lid bij succes.
	 */
	public function syncLid($profiel) {
		if (!$profiel instanceof Profiel) {
			$profiel = ProfielModel::get($profiel);
		}
		//kijk of het lid al bestaat in de googlecontacs-feed.
		$googleid = $this->existsInGoogleContacts($profiel);

		$error_message = '<div>Fout in Google-sync#%s: <br />' .
				'Lid: %s<br />Foutmelding: %s</div>';

		$doc = $this->createXML($profiel);

		if ($googleid != '') {
			try {
				//post to original entry's link[rel=self], set ETag in HTTP-headers for versioning
				$header = array('If-None-Match' => $this->getEtag($googleid));
				$entryResult = $this->gdata->updateEntry($doc->saveXML(), $this->getLinkSelf($googleid), null, $header);

				$photolink = $entryResult->getLink('http://schemas.google.com/contacts/2008/rel#photo')->getHref();
				$this->putPhoto($photolink, PICS_PATH . $profiel->getPasfotoPath(true));

				return 'Update: ' . $profiel->getNaam() . ' ';
			} catch (Exception $e) {
				return sprintf($error_message, 'update', $profiel->getNaam(), $e->getMessage());
			}
		} else {
			try {
				/* IF we insert an entry AFTER updating one, the Zend HttpClient 
				 * does not clear the headers and sends a If-None-Math along with
				 * the insert-request. Google complains about the method not 
				 * supporting concurrency:
				 * 		"501 POST method does not support concurrency"
				 * 
				 * Fixed by resetting the paramters with this magic line:
				 */
				$this->gdata->getHttpClient()->resetParameters($clearAll = true);

				$entryResult = $this->gdata->insertEntry($doc->saveXML(), GOOGLE_CONTACTS_URL);
				$photolink = $entryResult->getLink('http://schemas.google.com/contacts/2008/rel#photo')->getHref();
				$this->putPhoto($photolink, PICS_PATH . $profiel->getPasfotoPath(true));

				return 'Ingevoegd: ' . $profiel->getNaam() . ' ';
			} catch (Exception $e) {
				return sprintf($error_message, 'insert', $profiel->getNaam(), $e->getMessage());
			}
		}
	}

	/**
	 * Create a XML document for this Lid.
	 * @param $profiel create XML feed for this object
	 */
	private function createXML(Profiel $profiel) {

		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$entry = $doc->createElement('atom:entry');
		$entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:atom', 'http://www.w3.org/2005/Atom');
		$entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gd', 'http://schemas.google.com/g/2005');
		$entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gContact', 'http://schemas.google.com/contact/2008');
		$doc->appendChild($entry);

		// add name element
		$name = $doc->createElement('gd:name');
		$entry->appendChild($name);
		$fullName = $doc->createElement('gd:fullName', $profiel->getNaam());
		$name->appendChild($fullName);


		if ($this->extendedExport) {
			//nickname
			if ($profiel->nickname != '') {
				$nick = $doc->createElement('gContact:nickname', $profiel->nickname);
				$entry->appendChild($nick);
			}
			//duckname
			if ($profiel->duckname != '') {
				$duck = $doc->createElement('gContact:duckname', $profiel->duckname);
				$entry->appendChild($duck);
			}
			//initialen
			if ($profiel->voorletters != '') {
				$entry->appendChild($doc->createElement('gContact:initials', $profiel->voorletters));
			}
			//geslacht?
			$gender = $doc->createElement('gContact:gender');
			$gender->setAttribute('value', $profiel->geslacht == Geslacht::Man ? 'male' : 'female');
			//$entry->appendChild($gender);
		}

		//add home address
		if ($profiel->adres != '') {
			$address = $doc->createElement('gd:structuredPostalAddress');
			$address->setAttribute('primary', 'true');

			//only rel OR label (XOR) can (and must) be set
			if ($profiel->getWoonoord()) {
				$woonoord = $doc->createElement('gd:housename');
				$woonoord->appendChild(new DOMText($profiel->getWoonoord()->getNaam()));
				$address->appendChild($woonoord);
				$address->setAttribute('label', $profiel->getWoonoord()->getNaam());
			} else {
				$address->setAttribute('rel', 'http://schemas.google.com/g/2005#home');
			}

			$address->appendChild($doc->createElement('gd:street', $profiel->adres));
			if ($profiel->postcode != '') {
				$address->appendChild($doc->createElement('gd:postcode', $profiel->postcode));
			}
			$address->appendChild($doc->createElement('gd:city', $profiel->woonplaats));
			if ($profiel->land != '') {
				$address->appendChild($doc->createElement('gd:country', $profiel->land));
			}
			$address->appendChild($doc->createElement('gd:formattedAddress', $profiel->getFormattedAddress()));
			$entry->appendChild($address);
		}

		if ($this->extendedExport) {
			//adres ouders toevoegen, alleen bij leden...
			if ($profiel->isLid() AND $profiel->o_adres != '' AND $profiel->adres != $profiel->o_adres) {
				$address = $doc->createElement('gd:structuredPostalAddress');
				//$address->setAttribute('rel', 'http://schemas.google.com/g/2005#other');
				$address->setAttribute('label', 'Ouders');

				$address->appendChild($doc->createElement('gd:street', $profiel->o_adres));
				if ($profiel->o_postcode != '') {
					$address->appendChild($doc->createElement('gd:postcode', $profiel->o_postcode));
				}
				$address->appendChild($doc->createElement('gd:city', $profiel->o_woonplaats));
				if ($profiel->o_land != '') {
					$address->appendChild($doc->createElement('gd:country', $profiel->o_land));
				}
				$address->appendChild($doc->createElement('gd:formattedAddress', $profiel->getFormattedAddressOuders()));
				$entry->appendChild($address);
			}
		}

		// add email element
		$email = $doc->createElement('gd:email');
		$email->setAttribute('address', $profiel->getPrimaryEmail());
		$email->setAttribute('rel', 'http://schemas.google.com/g/2005#home');
		$email->setAttribute('primary', 'true');
		$entry->appendChild($email);

		if ($this->extendedExport) {
			// add IM adresses.
			$ims = array(
				array('msn', 'http://schemas.google.com/g/2005#MSN'),
				array('skype', 'http://schemas.google.com/g/2005#SKYPE'),
				array('icq', 'http://schemas.google.com/g/2005#ICQ'),
				array('jid', 'http://schemas.google.com/g/2005#JABBER')
			);
			foreach ($ims as $im) {
				if ($profiel->$im[0] != '') {
					$imEntry = $doc->createElement('gd:im');
					$imEntry->setAttribute('address', $profiel->$im[0]);
					$imEntry->setAttribute('protocol', $im[1]);
					$imEntry->setAttribute('rel', 'http://schemas.google.com/g/2005#home');
					$entry->appendChild($imEntry);
				}
			}
		}

		//phone numbers
		$telefoons = array();

		//ouders nummer...
		if ($this->extendedExport && $profiel->isLid()) {
			$telefoons[] = array('o_telefoon', 'http://schemas.google.com/g/2005#other');
		}
		$telefoons[] = array('telefoon', 'http://schemas.google.com/g/2005#home');
		$telefoons[] = array('mobiel', 'http://schemas.google.com/g/2005#mobile');

		foreach ($telefoons as $telefoon) {
			if ($profiel->$telefoon[0] != '') {
				$number = $doc->createElement('gd:phoneNumber', internationalizePhonenumber($profiel->$telefoon[0]));
				if ($telefoon[0] == 'mobiel') {
					$number->setAttribute('primary', 'true');
				}
				if ($telefoon[0] == 'o_telefoon') {
					$number->setAttribute('label', 'Ouders');
				} else {
					$number->setAttribute('rel', $telefoon[1]);
				}
				$entry->appendChild($number);
			}
		}

		if ($profiel->gebdatum != '' AND $profiel->gebdatum != '0000-00-00') {
			$geboortedatum = $doc->createElement('gContact:birthday');
			$geboortedatum->setAttribute('when', $profiel->gebdatum);
			$entry->appendChild($geboortedatum);
		}

		if ($this->extendedExport) {
			if ($profiel->website != '') {
				$website = $doc->createElement('gContact:website');

				$website->setAttribute('href', $profiel->website);
				$website->setAttribute('rel', 'home');
				$entry->appendChild($website);
			}

			if ($profiel->eetwens != '') {
				$eetwens = $doc->createElement('gContact:userDefinedField');
				$eetwens->setAttribute('key', 'Eetwens');
				$eetwens->setAttribute('value', $profiel->eetwens);
				$entry->appendChild($eetwens);
			}
		}

		//system group 'my contacts' er bij, als die bestaat..
		if ($this->getSystemGroupId('Contacts') !== null) {
			$systemgroup = $doc->createElement('gContact:groupMembershipInfo');
			$systemgroup->setAttribute('href', $this->getSystemGroupId('Contacts'));
			$entry->appendChild($systemgroup);
		}

		//in de groep $this->groepname
		$group = $doc->createElement('gContact:groupMembershipInfo');
		$group->setAttribute('href', $this->groupid);
		$entry->appendChild($group);


		//last updated
		if (LoginModel::mag('P_ADMIN')) {
			$update = $doc->createElement('gContact:userDefinedField');
			$update->setAttribute('key', 'update');
			$update->setAttribute('value', getDateTime());
			$entry->appendChild($update);
		}

		//csr uid
		$uid = $doc->createElement('gContact:userDefinedField');
		$uid->setAttribute('key', 'csruid');
		$uid->setAttribute('value', $profiel->uid);
		$entry->appendChild($uid);

		return $doc;
	}

	public static function isAuthenticated() {
		return isset($_SESSION['google_token']);
	}

	/**
	 * Vraag een Authsub-token aan bij google, plaats bij ontvangen in _SESSION['google_token'].
	 */
	public static function doRequestToken($self) {
		if (isset($_GET['token'])) {
			$_SESSION['google_token'] = Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
		}
		if (!isset($_SESSION['google_token'])) {
			$scope = 'https://www.google.com/m8/feeds';
			header('Location: ' . Zend_Gdata_AuthSub::getAuthSubTokenUri($self, $scope, 0, 1));
			exit;
		}
	}

}
