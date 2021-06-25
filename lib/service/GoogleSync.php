<?php

namespace CsrDelft\service;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\GoogleTokenRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use DOMDocument;
use DOMText;
use Exception;
use Google_Client;
use SimpleXMLElement;

define('GOOGLE_CONTACTS_URL', 'https://www.google.com/m8/feeds/contacts/default/full?v=3.0');
define('GOOGLE_GROUPS_URL', 'https://www.google.com/m8/feeds/groups/default/full?v=3.0');
define('GOOGLE_CONTACTS_BATCH_URL', 'https://www.google.com/m8/feeds/contacts/default/full/batch?v=3.0');

define('GOOGLE_CONTACTS_MAX_RESULTS', 1000);

/**
 * Documentatie voor Google Contacts API:
 * algemeen, interactie: https://developers.google.com/google-apps/contacts/v3/
 * alle referentie https://developers.google.com/google-apps/contacts/v3/reference
 *
 * Roep bij gebruik altijd eerst doRequestToken aan om te garanderen dat er een sessie is of gemaakt wordt
 * houdt er rekening mee dat een aanroep naar doRequestToken er voor kan zorgen dat de gebruiker naar de google
 * flow wordt gestuurd.
 */
class GoogleSync {
	/**
	 * Groep naam in Google Contacts.
	 *
	 * @var string
	 */
	private $groupname = 'C.S.R.-import';

	/**
	 * Alle groepen van de gebruiker.
	 *
	 * @var SimpleXMLElement[]
	 */
	private $groupFeed = null;

	/**
	 * Google-id van de groep waar alles in terecht moet komen.
	 *
	 * @var null|string
	 */
	private $groupid = null;

	/**
	 * Alle contacten in de 'Stek-groep' van de gebruiker
	 *
	 * @var SimpleXMLElement[]
	 */
	private $contactFeed = null;

	/**
	 * An array containing array's with some data for each contact.
	 * @var array|null
	 */
	private $contactData = null;

	/**
	 * @var Google_Client
	 */
	private $client;
	/**
	 * @var GoogleTokenRepository
	 */
	private $googleTokenRepository;

	/**
	 * GoogleSync constructor.
	 * @param GoogleTokenRepository $googleTokenRepository
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function __construct(GoogleTokenRepository $googleTokenRepository) {
		$this->googleTokenRepository = $googleTokenRepository;
	}

	public function init() {
		$google_token = $this->googleTokenRepository->find(LoginService::getUid());
		if (!$google_token) {
			throw new CsrException('Authsub token not available, use doRequestToken.');
		}

		if (lid_instelling('googleContacts', 'groepnaam') != '') {
			$this->groupname = trim(lid_instelling('googleContacts', 'groepnaam'));
			if ($this->groupname == '') {
				$this->groupname = 'C.S.R.-import';
			}
		}

		$this->client = self::createGoogleCLient();
		$this->client->fetchAccessTokenWithRefreshToken($google_token->token);

		try {
			//first load group feed, find or create the groupname from the user settings.
			$this->loadGroupFeed();
			$this->groupid = $this->getGroupId();

			//then load the contacts for this group.
			$this->loadContactsForGroup($this->groupid);
		} catch (CsrException $e) {
			triggerExceptionAsWarning($e);
			$this->googleTokenRepository->delete($google_token);
			throw new CsrGebruikerException("Google synchronisatie mislukt");
		}
	}

	/**
	 * Load all contactgroups.
	 */
	private function loadGroupFeed() {
		$httpClient = $this->client->authorize();
		$response = $httpClient->request('GET', GOOGLE_GROUPS_URL);
		if ($response->getStatusCode() === 401) {
			throw new CsrException("Code 401 response on GOOGLE_GROUPS_URL");
		}
		$this->groupFeed = $this->loadXmlString($response->getBody())->entry;
	}

	/**
	 * Load contacts from certain contact group.
	 */
	private function loadContactsForGroup($groupId) {
		// Default max-results is 25, laad alles in 1 keer
		$httpClient = $this->client->authorize();
		$response = $httpClient->request('GET', GOOGLE_CONTACTS_URL . '&max-results=1000&group=' . urlencode($groupId));
		if ($response->getStatusCode() === 401) {
			throw new CsrException();
		}
		$this->contactFeed = $this->loadXmlString($response->getBody())->entry;
	}

	/**
	 * Zorg ervoor dat $xml met xpath doorzocht kan worden. Dit werkt alleen voor het huidige element,
	 * diepere elementen moeten opnieuw gefixt worden.
	 *
	 * De standaard namespace wordt _, omdat deze niet leeg kan zijn.
	 *
	 * @param $xml SimpleXMLElement
	 */
	private function fixSimpleXMLNameSpace($xml) {
		foreach ($xml->getDocNamespaces() as $strPrefix => $strNamespace) {
			if (strlen($strPrefix) == 0) {
				$strPrefix = "_";
			}
			$xml->registerXPathNamespace($strPrefix, $strNamespace);
		}
	}

	/**
	 * Trek naam googleId en wat andere relevante meuk uit de feed-objecten
	 * en stop dat in een array.
	 */
	public function getGoogleContacts() {
		if ($this->contactData == null) {
			$this->contactData = array();
			foreach ($this->contactFeed as $contact) {
				$unpacked = $this->unpackGoogleContact($contact);
				if ($unpacked) {
					$this->contactData[] = $unpacked;
				}
			}
		}

		return $this->contactData;
	}

	/**
	 * Maak een behapbaar object van een contact.
	 *
	 * @param $contact SimpleXMLElement
	 *
	 * @return array|null Een array als de contact een csruid heeft, anders null
	 */
	private function unpackGoogleContact($contact) {
		$this->fixSimpleXMLNameSpace($contact);

		$uid = $contact->xpath('gContact:userDefinedField[@key="csruid"]');
		if (empty($uid)) {
			return null;
		} // Geen Uid, niet van ons.
		$uid = $uid[0];
		$link = $contact->xpath('_:link[@rel="self"]')[0];
		$photoLink = $contact->xpath('_:link[@rel="http://schemas.google.com/contacts/2008/rel#photo"]')[0];

		return array(
			'name' => (string)$contact->title,
			'etag' => (string)$contact->attributes('gd', true)->etag,
			'id' => (string)$contact->id,
			'self' => (string)$link->attributes()->href,
			'photo' => array(
				'href' => (string)$photoLink->attributes()->href,
				'etag' => (string)$photoLink->attributes('gd', true)->etag
			),
			'csruid' => (string)$uid->attributes()->value
		);
	}

	/**
	 * Check of een Lid al voorkomt in de lijst met contacten zoals ingeladen van google.
	 *
	 * @param $profiel Profiel waarvan de aanwezigheid gechecked moet worden.
	 *
	 * @return null|array met het google-id in het geval van voorkomen, anders null.
	 */
	public function existsInGoogleContacts(Profiel $profiel) {
		if (!$this->isAuthenticated()) {
			return null;
		}

		$name = strtolower($profiel->getNaam());
		foreach ($this->getGoogleContacts() as $contact) {

			if (
				$contact['csruid'] == $profiel->uid ||
				strtolower($contact['name']) == $name ||
				str_replace(' ', '', strtolower($contact['name'])) == str_replace(' ', '', $name)
			) {
				return $contact;
			}
		}
		return null;
	}

	/**
	 * return the etag for any matching contact in this->contactFeed.
	 */
	public function getEtag($googleid) {
		foreach ($this->getGoogleContacts() as $contact) {
			if (strtolower($contact['self']) == $googleid) {
				return $contact['etag'];
			}
		}
		return null;
	}

	/**
	 * Get array with group[name] => id
	 */
	public function getGroups() {
		$return = array();
		foreach ($this->groupFeed as $group) {
			$this->fixSimpleXMLNameSpace($group);

			$title = (string)$group->title;

			if (substr($title, 0, 13) == 'System Group:') {
				$title = substr($title, 14);
			}
			//viesss, check of er een SystemGroup-tag bestaat, zo ja, het systemgroupid
			//opslaan in de array.
			//Dit ID hebben we nodig om onafhankelijk van de ingestelde taal @google de system
			//group 'My Contacts' te kunnen gebruiken
			$systemgroup = $group->xpath('gContact:systemGroup');
			if (count($systemgroup) == 1) {
				$systemgroup = (string)$systemgroup[0]->id;
			} else {
				$systemgroup = null;
			}

			$return[] = array(
				'id' => (string)$group->id,
				'name' => $title,
				'systemgroup' => $systemgroup
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

		$httpClient = $this->client->authorize();
		$response = $httpClient->request('POST', GOOGLE_GROUPS_URL, [
			'headers' => ['Content-Type' => 'application/atom+xml'],
			'body' => $doc->saveXML()
		]);

		//herlaad groupFeed om de nieuw gemaakte daar ook in te hebben.
		$this->loadGroupFeed();

		return (string)$this->loadXmlString($response->getBody())->id;
	}

	/**
	 * Een hele serie leden syncen naar google contacts.
	 *
	 * @param $leden Profiel[] array van uid's of Lid-objecten die moeten worden gesynced
	 *
	 * @return string met foutmeldingen en de namen van de gesyncte leden.
	 */
	public function syncLidBatch($leden) {
		//kan veel tijd kosten, dus time_limit naar 0 zodat het oneindig door kan gaan.
		set_time_limit(0);

		/** @var Profiel[] $profielBatch */
		$profielBatch = [];
		foreach ($leden as $profiel) {
			if ($profiel instanceof Profiel) {
				$profielBatch[] = $profiel;
			} else {
				$profiel = ProfielRepository::get($profiel);
				if ($profiel) {
					$profielBatch[] = $profiel;
				}
			}
		}
		$message = '';

		# Google contacts api kan max 100 per keer.
		$chunks = array_chunk($profielBatch, 100);
		foreach ($chunks as $profielBatch) {
			$doc = new DOMDocument();
			$doc->formatOutput = true;
			$feed = $doc->createElement('atom:feed');
			$feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:batch', 'http://schemas.google.com/gdata/batch');
			$feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:atom', 'http://www.w3.org/2005/Atom');
			$feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gd', 'http://schemas.google.com/g/2005');
			$feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gContact', 'http://schemas.google.com/contact/2008');


			$doc->appendChild($feed);

			foreach ($profielBatch as $profiel) {
				$profielXml = $doc->importNode($this->createXML($profiel)->documentElement, true);
				$feed->appendChild($profielXml);

				$batchOperation = $doc->createElement('batch:operation');

				$contact = $this->existsInGoogleContacts($profiel);

				if ($contact != null) {
					$etag = $this->getEtag($contact['self']);
					$etagAttribute = $doc->createAttribute('gd:etag');
					$etagAttribute->nodeValue = $etag;
					$profielXml->appendChild($etagAttribute);

					$id = $doc->createElement('atom:id');
					$id->nodeValue = $contact['id'];
					$profielXml->appendChild($id);

					$batchOperation->setAttribute('type', 'update');
					$message .= 'Update: ' . $profiel->getNaam() . ' ';
				} else {
					$batchOperation->setAttribute('type', 'insert');
					$message .= 'Ingevoegd: ' . $profiel->getNaam() . ' ';
				}

				$profielXml->appendChild($batchOperation);
			}

			$httpClient = $this->client->authorize();
			$response = $httpClient->request('POST', GOOGLE_CONTACTS_BATCH_URL, [
				'headers' => [
					'Content-Type' => 'application/atom+xml',
					'GData-Version' => '3.0'],
				'body' => $doc->saveXML()
			]);

			$newContacts = $this->loadXmlString($response->getBody());

			foreach ($newContacts->entry as $contact) {
				$this->fixSimpleXMLNameSpace($contact);

				$contact = $this->unpackGoogleContact($contact);
				$profiel = ProfielRepository::get($contact['csruid']);
				if ($profiel) {
					$this->updatePhoto($contact, $profiel);
				}
			}
		}

		return $message;
	}

	/**
	 * Een enkel lid syncen naar Google contacts.
	 *
	 * @param $profiel Profiel
	 *
	 * @return string met foutmelding of naam van lid bij succes.
	 * @throws CsrException
	 */
	public function syncLid(Profiel $profiel) {
		if (!$profiel instanceof Profiel) {
			$profiel = ProfielRepository::get($profiel);
		}

		//kijk of het lid al bestaat in de googlecontacs-feed.
		$googleid = $this->existsInGoogleContacts($profiel);

		$error_message = '<div>Fout in Google-sync#%s: <br />' .
			'Lid: %s<br />Foutmelding: %s</div>';

		$doc = $this->createXML($profiel);

		$httpClient = $this->client->authorize();

		if ($googleid != null) {
			try {
				//post to original entry's link[rel=self], set ETag in HTTP-headers for versioning
				$response = $httpClient->request('PUT', $googleid['self'], [
					'headers' => [
						'GData-Version' => '3.0',
						'Content-Type' => 'application/atom+xml',
						'If-Match' => $googleid['etag']
					],
					'body' => $doc->saveXML()
				]);

				$contact = $this->unpackGoogleContact($this->loadXmlString($response->getBody()));
				$this->updatePhoto($contact, $profiel);

				return 'Update: ' . $profiel->getNaam() . ' ';
			} catch (Exception $e) {
				throw new CsrException(sprintf($error_message, 'update', $profiel->getNaam(), $e->getMessage()));
			}
		} else {
			try {
				$response = $httpClient->request('POST', GOOGLE_CONTACTS_URL, [
					'headers' => [
						'Content-Type' => 'application/atom+xml'
					],
					'body' => $doc->saveXML()
				]);

				$contact = $this->unpackGoogleContact($this->loadXmlString($response->getBody()));
				$this->updatePhoto($contact, $profiel);

				return 'Ingevoegd: ' . $profiel->getNaam() . ' ';
			} catch (Exception $e) {
				throw new CsrException(sprintf($error_message, 'insert', $profiel->getNaam(), $e->getMessage()));
			}
		}
	}

	/**
	 * Haal de link naar de contact foto uit een contact xml-string en post de foto van $profiel er naar toe.
	 *
	 * @param $contact array
	 * @param $profiel Profiel
	 */
	private function updatePhoto($contact, $profiel) {

		$url = $contact['photo']['href'];

		$path = $profiel->getPasfotoInternalPath(true);

		if ($path === null) {
			return;
		}

		$headers = array('GData-Version' => '3.0', 'Content-Type' => "image/*");

		if ($contact['photo']['etag'] != '') {
			$headers = array('If-Match' => $contact['photo']['etag']) + $headers;
		}

		$httpClient = $this->client->authorize();

		$httpClient->request('PUT', $url, [
			'headers' => $headers,
			'body' => file_get_contents($path)
		]);
	}

	/**
	 * Create a XML document for this Lid.
	 *
	 * @param $profiel Profiel create XML feed for this object
	 *
	 * @return DOMDocument XML document voor dit Profiel
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

		//nickname
		if ($profiel->nickname != '') {
			$nick = $doc->createElement('gContact:nickname', $profiel->nickname);
			$entry->appendChild($nick);
		}
		//initialen
		if ($profiel->voorletters != '') {
			$entry->appendChild($doc->createElement('gContact:initials', $profiel->voorletters));
		}

		//add home address
		if ($profiel->adres != '') {
			$address = $doc->createElement('gd:structuredPostalAddress');
			$address->setAttribute('primary', 'true');

			//only rel OR label (XOR) can (and must) be set
			$woonoord = $profiel->getWoonoord();
			if ($woonoord) {
				$house = $doc->createElement('gd:housename');
				$house->appendChild(new DOMText($woonoord->naam));
				$address->appendChild($house);
				$address->setAttribute('label', $woonoord->naam);
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

		// add email element
		$email = $doc->createElement('gd:email');
		$email->setAttribute('address', $profiel->getPrimaryEmail());
		$email->setAttribute('rel', 'http://schemas.google.com/g/2005#home');
		$email->setAttribute('primary', 'true');
		$entry->appendChild($email);

		//phone numbers
		$telefoons = array();

		$telefoons[] = array('telefoon', 'http://schemas.google.com/g/2005#home');
		$telefoons[] = array('mobiel', 'http://schemas.google.com/g/2005#mobile');

		foreach ($telefoons as $telefoon) {
			if ($profiel->{$telefoon[0]} != '') {
				$number = $doc->createElement('gd:phoneNumber', internationalizePhonenumber($profiel->{$telefoon[0]}));
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

		if ($profiel->gebdatum != null && date_format_intl($profiel->gebdatum, DATE_FORMAT) != '0000-00-00') {
			$geboortedatum = $doc->createElement('gContact:birthday');
			$geboortedatum->setAttribute('when', date_format_intl($profiel->gebdatum, DATE_FORMAT));
			$entry->appendChild($geboortedatum);
		}

		if ($profiel->website != '') {
			$website = $doc->createElement('gContact:website');

			$website->setAttribute('href', $profiel->website);
			$website->setAttribute('rel', 'home');
			$entry->appendChild($website);
		}

		//system group 'my contacts' er bij, als die bestaat..
		if ($this->getSystemGroupId('Contacts') !== null) {
			$systemgroup = $doc->createElement('gContact:groupMembershipInfo');
			$systemgroup->setAttribute('href', $this->getSystemGroupId('Contacts'));
			$entry->appendChild($systemgroup);
		}

		//in de groep $this->groepname
		// Veranderen van een contact kan dit element niet bevatten.
		$group = $doc->createElement('gContact:groupMembershipInfo');
		$group->setAttribute('href', $this->groupid);
		$entry->appendChild($group);


		//last updated
		if (LoginService::mag(P_ADMIN)) {
			$update = $doc->createElement('gContact:userDefinedField');
			$update->setAttribute('key', 'update');
			$update->setAttribute('value', getDateTime());
			$entry->appendChild($update);
		}

		$profielPagina = $doc->createElement('gContact:website');
		$profielPagina->setAttribute('href', getCsrRoot() . '/profiel/' . $profiel->uid);
		$profielPagina->setAttribute('label', 'C.S.R. webstek profiel');
		$entry->appendChild($profielPagina);

		// csr uid, wordt gebruikt bij update.
		$uid = $doc->createElement('gContact:userDefinedField');
		$uid->setAttribute('key', 'csruid');
		$uid->setAttribute('value', $profiel->uid);
		$entry->appendChild($uid);

		return $doc;
	}

	/**
	 * Bestaat er een verbinding met Google?
	 *
	 * @return bool
	 */
	public function isAuthenticated() {
		return $this->googleTokenRepository->exists(LoginService::getUid());
	}

	/**
	 * Vraag een Authsub-token aan bij google, plaats bij ontvangen in GoogleToken tabel.
	 *
	 * @param $state string, moet de url bevatten waar naar geredirect moet worden als
	 * de authenticatie gelukt is, de url zonder `addToGoogleContacts` wordt gebruikt als
	 * de authenticatie mislukt.
	 * @throws CsrException
	 */
	public function doRequestToken($state) {
		if (!$this->isAuthenticated()) {
			$client = self::createGoogleCLient();
			$client->setState(urlencode($state));

			$googleImportUrl = $client->createAuthUrl();
			header("HTTP/1.0 307 Temporary Redirect");
			header("Location: $googleImportUrl");
			exit;
		} else {
			$this->init();
		}
	}

	/**
	 * Maak een nieuwe Google_Client met de settings van de stek.
	 *
	 * @return Google_Client
	 * @throws CsrException
	 */
	public static function createGoogleCLient() {
		$request = ContainerFacade::getContainer()->get('request_stack')->getCurrentRequest();
		$redirect_uri = $request->getSchemeAndHttpHost() . '/google/callback';
		$client = new Google_Client();
		$client->setApplicationName('Stek');
		$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
		$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
		$client->setRedirectUri($redirect_uri);
		$client->setAccessType('offline');
		// Zonder force kunnen we nog een oude sessie krijgen (zonder refresh token)
		$client->setApprovalPrompt('force');
		$client->setScopes(['https://www.google.com/m8/feeds']);

		return $client;
	}

	public function loadXmlString($xml) {

		$prev = libxml_use_internal_errors(false);
		$data = @simplexml_load_string($xml);
		if ($data === false) {
			throw new CsrException("Error parsing xml. Content was: ".substr ( $xml, 0, 100)."...");
		}
		libxml_use_internal_errors($prev);
		return $data;
	}
}
