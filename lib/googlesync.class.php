<?php
require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata');
Zend_Loader::loadClass('Zend_Gdata_AuthSub');
Zend_Loader::loadClass('Zend_Http_Client');
Zend_Loader::loadClass('Zend_Uri_Http');
Zend_Loader::loadClass('Zend_Gdata_Query');
Zend_Loader::loadClass('Zend_Gdata_Feed');

/*
 * Documentatie voor google GData protocol:
 * algemeen, interactie: http://code.google.com/apis/contacts/docs/3.0/developers_guide_protocol.html
 * gd-namespace: http://code.google.com/apis/gdata/docs/2.0/elements.html
 * gContact-namespace: http://code.google.com/apis/contacts/docs/3.0/reference.html
 */
class GoogleSync{

	private $groupname='C.S.R.-import';
	
	private $gdata=null;
	
	//feed contents
	private $contactFeed=null;
	private $groupFeed=null;
	
	public function __construct(){
		if(!isset($_SESSION['google_token'])){
			throw new Exception('Authsub token not available');
		}
		
		//$client=Zend_Gdata_ClientLogin::getHttpClient($user, $password, 'cp');
		$client=Zend_Gdata_AuthSub::getHttpClient($_SESSION['google_token']);

		//$client->setHeaders('If-Match: *'); //delete or update only if not changed since it was last read.
		$this->gdata=new Zend_Gdata($client);
		$this->gdata->setMajorProtocolVersion(3);
	
		$this->loadContactFeed();
		$this->loadGroupFeed();

	}

	/* Laad de contact-feed in van google.
	 */
	private function loadContactFeed(){
		$query = new Zend_Gdata_Query('http://www.google.com/m8/feeds/contacts/default/full?max-results=400');
		$this->googleContacts=$this->gdata->getFeed($query);
	}
	/* Laad de group-feed in van google.
	 */
	private function loadGroupFeed(){
		$query=new Zend_Gdata_Query('http://www.google.com/m8/feeds/groups/default/full');
		$this->groupFeed=$this->gdata->getFeed($query);
	}
	
	/* Trek naam en google-id uit de feed, de rest is niet echt nodig.
	 */
	public function getGoogleContacts(){
		$return=array();
		foreach($this->googleContacts as $contact){
			//typecast naar string, dan komt het relevante veld uit het Zend-objectje rollen
			$return[]=array(
				'name'=>(string)$contact->title,
				'id'=>(string)$contact->id,
				'xml'=>mb_htmlentities(str_replace('><', ">\n<", $contact->getXML()))
			);
		}
		return $return;
	}

	
	/* plaats een foto voor een google contact.
	 *
	 * @param $photolink link uit een google-entry waar de foto naartoe moet.
	 * @param $filename bestandsnaam van de foto die moet worden opgestuurd.
	 */
	private function putPhoto($photolink, $filename){
		$this->gdata->put(file_get_contents($filename), $photolink, null, 'image/*');
	}

	/* Check of de naam al voorkomt in de lijst met contacten zoals ingeladen
	 * van google.
	 *
	 * @param $name Naam van het contact dat moet worden gecontroleerd.
	 *
	 * @return string met het google-id in het geval van voorkomen, anders null.
	 */
	public function existsInGoogleContacts($name){
		$name=strtolower($name);
		foreach($this->getGoogleContacts() as $contact){
			if(strtolower($contact['name'])==$name){
				return $contact['id'];

			//zonder spaties kijken...
			}elseif(str_replace(' ', '', strtolower($contact['name'])) == str_replace(' ', '', $name)){
				return $contact['id'];
			}
		}
		return null;
	}
	
	/*
	 * Get the groupid for the group $this->groupname, or create and return groupname.
	 *
	 * @return string met het google group-id.
	 */
	private function getGroupId($groupname=null){
		if($groupname==null){
			$groupname=$this->groupname;
		}
		//kijken of we al een grop hebben met de naam
		foreach($this->groupFeed as $group){
			if((string)$group->title==$groupname){
				return (string)$group->id;
			}
		}
		
		//zo niet, dan maken deze groep nieuw aan.
		$doc=new DOMDocument();
		$doc->formatOutput=true;
		$entry = $doc->createElement('atom:entry');
		$entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:atom', 'http://www.w3.org/2005/Atom');
		$entry->setAttributeNS('http://www.w3.org/2000/xmlns/' , 'xmlns:gd', 'http://schemas.google.com/g/2005');
		$doc->appendChild($entry);
		
		$title=$doc->createElement('atom:title', $groupname);
		$title->setAttribute('type', 'text');
		$entry->appendChild($title);
		
		$response=$this->gdata->insertEntry($doc->saveXML(), 'http://www.google.com/m8/feeds/groups/default/full');
		
		//herlaad groupFeed om de nieuw gemaakte daar ook in te hebben.
		$this->loadGroupFeed();
		
		return (string)$response->id;
	}

	/* Een hele serie leden syncen naar google contacts.
	 *
	 * @param $leden array van uid's of Lid-objecten die moeten worden gesynced
	 * 
	 * @return string met foutmeldingen en de namen van de gesyncte leden.
	 */
	public function syncLidBatch($leden){
		$lidBatch=array();
		foreach($leden as $lid){
			if($lid instanceof Lid){
				$lidBatch[]=$lid;
			}else{
				try{
					$lidBatch[]=LidCache::getLid($lid);
				}catch(Exception $e){
					// omit faulty uid's
				}
			}
		}
		$message='';

		//dit zou netjes kunnen door één xml-bestand te maken en dat één 
		//keer te posten, maar daar heb ik nu even geen zin in.
		//btw: google heeft een batch-limit van 100 acties.
		//zie ook: http://code.google.com/apis/gdata/docs/batch.html
		foreach($lidBatch as $lid){
			$message.=$this->syncLid($lid).', ';
		}
		return $message;
	}
	
	/* Een enkel lid syncen naar Google contacts.
	 *
	 * @param $lid uid of Lid-object
	 *
	 * @return string met foutmelding of naam van lid bij succes.
	 */
	public function syncLid($lid){
		if(!$lid instanceof Lid){
			$lid=LidCache::getLid($lid);
		}
		$googleid=$this->existsInGoogleContacts($lid->getNaam());

		if($googleid!=''){
			//update
			//echo '<br /> updating '.$lid->getNaam().' -- not yet implemented, omitting <br />';
			return $lid->getNaam().' bestaat al.';

		}else{
		
			//insert.
			$doc=new DOMDocument();
			$doc->formatOutput = true;
			$entry = $doc->createElement('atom:entry');
			$entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:atom', 'http://www.w3.org/2005/Atom');
			$entry->setAttributeNS('http://www.w3.org/2000/xmlns/' , 'xmlns:gd', 'http://schemas.google.com/g/2005');
			$entry->setAttributeNS('http://www.w3.org/2000/xmlns/' , 'xmlns:gContact', 'http://schemas.google.com/contact/2008');
			$doc->appendChild($entry);

			// add name element
			$name = $doc->createElement('gd:name');
			$entry->appendChild($name);
			$fullName = $doc->createElement('gd:fullName', $lid->getNaam());
			$name->appendChild($fullName);

			//nickname
			if($lid->getNickname()!=''){
				$nick=$doc->createElement('gContact:nickname', $lid->getNickname());
				$entry->appendChild($nick);
			}
			
			//add home address
			if($lid->getProperty('adres')!=''){
				$address=$doc->createElement('gd:structuredPostalAddress');
				$address->setAttribute('primary', 'true');

				//only rel OR label (XOR) can (and must) be set
				if($lid->getWoonoord() instanceof Groep){
					$address->appendChild($doc->createElement('gd:housename', $lid->getWoonoord()->getNaam()));
					$address->setAttribute('label', $lid->getWoonoord()->getNaam());
				}else{
					$address->setAttribute('rel', 'http://schemas.google.com/g/2005#home');
				}
				$address->appendChild($doc->createElement('gd:street', $lid->getProperty('adres')));
				if($lid->getProperty('postcode')!=''){
					$address->appendChild($doc->createElement('gd:postcode', $lid->getProperty('postcode')));
				}
				$address->appendChild($doc->createElement('gd:city', $lid->getProperty('woonplaats')));
				if($lid->getProperty('land')!=''){
					$address->appendChild($doc->createElement('gd:country', $lid->getProperty('land')));
				}
				$address->appendChild($doc->createElement('gd:formattedAddress', $lid->getFormattedAddress()));
				$entry->appendChild($address);
			}

			//adres ouders toevoegen, alleen bij leden...
			if($lid->isLid() AND $lid->getProperty('o_adres')!='' AND $lid->getProperty('adres')!=$lid->getProperty('o_adres')){
				$address=$doc->createElement('gd:structuredPostalAddress');
				//$address->setAttribute('rel', 'http://schemas.google.com/g/2005#other');
				$address->setAttribute('label', 'Ouders');

				$address->appendChild($doc->createElement('gd:street', $lid->getProperty('o_adres')));
				if($lid->getProperty('o_postcode')!=''){
					$address->appendChild($doc->createElement('gd:postcode', $lid->getProperty('o_postcode')));
				}				
				$address->appendChild($doc->createElement('gd:city', $lid->getProperty('o_woonplaats')));
				if($lid->getProperty('o_land')!=''){
					$address->appendChild($doc->createElement('gd:country', $lid->getProperty('o_land')));
				}
				$address->appendChild($doc->createElement('gd:formattedAddress', $lid->getFormattedAddress($ouders=true)));
				$entry->appendChild($address);
			}
				
			
			// add email element
			$email=$doc->createElement('gd:email');
			$email->setAttribute('address' , $lid->getEmail());
			$email->setAttribute('rel' ,'http://schemas.google.com/g/2005#home');
			$email->setAttribute('primary', 'true');
			$entry->appendChild($email);
			
			// add IM adresses.
			$ims=array(
				array('msn', 'http://schemas.google.com/g/2005#MSN'),
				array('skype', 'http://schemas.google.com/g/2005#SKYPE'),
				array('icq', 'http://schemas.google.com/g/2005#ICQ'),
				array('jid', 'http://schemas.google.com/g/2005#JABBER')
			);
			foreach($ims as $im){
				if($lid->getProperty($im[0])!=''){
					$imEntry=$doc->createElement('gd:im');
					$imEntry->setAttribute('address', $lid->getProperty($im[0]));
					$imEntry->setAttribute('protocol', $im[1]);
					$imEntry->setAttribute('rel', 'http://schemas.google.com/g/2005#home');
					$entry->appendChild($imEntry);
				}
			}

			//add phone numbers
			$telefoons=array(
				array('telefoon', 'http://schemas.google.com/g/2005#home'),
				array('mobiel', 'http://schemas.google.com/g/2005#mobile'),
			);
			//als het een huidig lid betreft ook het nummer van de ouders erin.
			if($lid->isLid()){
				$telefoons[]=array('o_telefoon', 'label:Ouders');
			}
			foreach($telefoons as $telefoon){
				if($lid->getProperty($telefoon[0])!=''){
					$number=$doc->createElement('gd:phoneNumber', internationalizePhonenumber($lid->getProperty($telefoon[0])));
					if($telefoon[0]=='telefoon'){
						$number->setAttribute('primary', 'true');
					}
					if(substr($telefoon[1],0, 5)=='label'){
						$number->setAttribute('label', substr($telefoon[1],6));
					}else{
						$number->setAttribute('rel', $telefoon[1]);
					}
					$entry->appendChild($number);
				}
			}

			if($lid->getGeboortedatum()!='' AND $lid->getGeboortedatum()!='0000-00-00' ){
				$geboortedatum=$doc->createElement('gContact:birthday');
				$geboortedatum->setAttribute('when', $lid->getGeboortedatum());
				$entry->appendChild($geboortedatum);
			}
			
			if($lid->getProperty('website')!=''){
				$website=$doc->createElement('gContact:website');
				
				$website->setAttribute('href', $lid->getProperty('website'));
				$website->setAttribute('rel', 'home');
				$entry->appendChild($website);
			}
			
			if($lid->getProperty('eetwens')!=''){
				$eetwens=$doc->createElement('gContact:userDefinedField');
				$eetwens->setAttribute('key', 'Eetwens');
				$eetwens->setAttribute('value', $lid->getProperty('eetwens'));
				$entry->appendChild($eetwens);
			}
			//system group 'my contacts' er bij.
			$systemgroup=$doc->createElement('gContact:groupMembershipInfo');
			$systemgroup->setAttribute('href', $this->getGroupId('My Contacts'));
			$entry->appendChild($systemgroup);

			//in de groep $this->groepname en in de system group my contacts stoppen
			$group=$doc->createElement('gContact:groupMembershipInfo');
			$group->setAttribute('href', $this->getGroupId());
			$entry->appendChild($group);

			try{
				//echo $doc->saveXML();
				$entryResult = $this->gdata->insertEntry($doc->saveXML(), 'http://www.google.com/m8/feeds/contacts/default/full');
				$photolink=$entryResult->getLink('http://schemas.google.com/contacts/2008/rel#photo')->getHref();
				$this->putPhoto($photolink, PICS_PATH.'/'.$lid->getPasfotoPath($square=true));
				return $lid->getNaam();
			}catch(Exception $e){
				return 'Fout in Google-sync (graag even mailen naar PubCie): <br /> invoeren van lid: '.$lid->getNaam().'<br />Foutmelding: '.$e->getMessage().'<br />';
			}
			
			
		}
	}
	
	/*
	 * Vraag een Authsub-token aan bij google, plaats bij ontvangen in _SESSION['google_token'].
	 */
	public static function doRequestToken($self){
		if(isset($_GET['token'])){
			$_SESSION['google_token']=Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
		}
		if(!isset($_SESSION['google_token'])){
			$scope = 'http://www.google.com/m8/feeds';
			header('Location: '.Zend_Gdata_AuthSub::getAuthSubTokenUri($self, $scope, 0, 1));
			exit;
		}
	}
}
