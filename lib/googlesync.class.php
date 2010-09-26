<?php
require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
Zend_Loader::loadClass('Zend_Gdata_AuthSub');
Zend_Loader::loadClass('Zend_Http_Client');
Zend_Loader::loadClass('Zend_Uri_Http');
Zend_Loader::loadClass('Zend_Gdata_Query');
Zend_Loader::loadClass('Zend_Gdata_Feed');

class GoogleSync{

	private $groupname='C.S.R.';
	
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
	
	private function loadContactFeed(){
		$query = new Zend_Gdata_Query('http://www.google.com/m8/feeds/contacts/default/full');
		$this->googleContacts=$this->gdata->getFeed($query);
	}
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
	
	private function putPhoto($photolink, $filename){
		$this->gdata->put(file_get_contents($filename), $photolink, null, 'image/*');
	}
	public function existsInGoogleContacts($name){
		$name=strtolower($name);
		foreach($this->getGoogleContacts() as $contact){
			if(strtolower($contact['name'])==$name){
				return $contact['id'];
			}elseif(str_replace(' ', '', strtolower($contact['name'])) == str_replace(' ', '', $name)){
				return $contact['id'];
			}
		}
		return null;
	}
	/*
	 * Get the groupid for the group $this->groupname, or create and return groupname.
	 */
	private function getGroupId(){
		foreach($this->groupFeed as $group){
			if((string)$group->title==$this->groupname){
				return (string)$group->id;
			}
		}
		$doc=new DOMDocument();
		$doc->formatOutput=true;
		$entry = $doc->createElement('atom:entry');
		$entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:atom', 'http://www.w3.org/2005/Atom');
		$entry->setAttributeNS('http://www.w3.org/2000/xmlns/' , 'xmlns:gd', 'http://schemas.google.com/g/2005');
		$doc->appendChild($entry);
		
		$title=$doc->createElement('atom:title', $this->groupname);
		$title->setAttribute('type', 'text');
		$entry->appendChild($title);
		
		$response=$this->gdata->insertEntry($doc->saveXML(), 'http://www.google.com/m8/feeds/groups/default/full');
		$this->loadGroupFeed();
		
		return (string)$response->id;
	}
	
	public function syncLid($lid){
		if(!$lid instanceof Lid){
			$lid=LidCache::getLid($lid);
		}
		$googleid=$this->existsInGoogleContacts($lid->getNaam());
		echo $googleid;
		if($googleid!==null){
			//update
			//echo '<br /> updating '.$lid->getNaam().' -- not yet implemented, omitting <br />';
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

			if($lid->getNickname()!=''){
				$nick=$doc->createElement('gContact:nickname', $lid->getNickname());
				$entry->appendChild($nick);
			}
			//add home address
			$address=$doc->createElement('gd:structuredPostalAddress');
			$address->setAttribute('rel', 'http://schemas.google.com/g/2005#home');
			if($lid->getWoonoord() instanceof Groep){
				$address->appendChild($doc->createElement('gd:housename', $lid->getWoonoord()->getNaam()));
			}
			$address->appendChild($doc->createElement('gd:street', $lid->getProperty('adres')));
			$address->appendChild($doc->createElement('gd:postcode', $lid->getProperty('postcode')));
			$address->appendChild($doc->createElement('gd:city', $lid->getProperty('woonplaats')));
			$address->appendChild($doc->createElement('gd:country', $lid->getProperty('land')));
			$address->appendChild($doc->createElement('gd:formattedAddress', $lid->getProperty('adres')."\n".$lid->getProperty('postcode')." ".$lid->getProperty('woonplaats')."\n".$lid->getProperty('land')));
			$entry->appendChild($address);
			
			// add email element
			$email=$doc->createElement('gd:email');
			$email->setAttribute('address' , $lid->getEmail());
			$email->setAttribute('rel' ,'http://schemas.google.com/g/2005#home');
			$entry->appendChild($email);
			
			
			$ims=array(
				array('msn', 'http://schemas.google.com/g/2005#MS'),
				array('skype', 'http://schemas.google.com/g/2005#SKYPE'),
				array('icq', 'http://schemas.google.com/g/2005#ICQ'),
				array('jid', 'http://schemas.google.com/g/2005#JABBER')
			);
			
			foreach($ims as $im){
				if($lid->getProperty($im[0])!=''){
					$imentry=$doc->createElement('gd:im');
					$imentry->setAttribute('address', $lid->getProperty($im[0]));
					$imentry->setAttribute('protocol', $im[1]);
					//$doc->appendChild($imentry);
				}
			}
			
			$telefoons=array(
				array('telefoon', 'http://schemas.google.com/g/2005#home'),
				array('mobiel', 'http://schemas.google.com/g/2005#mobile')
			);
			
			foreach($telefoons as $telefoon){
				if($lid->getProperty($telefoon[0])!=''){
					$number=$doc->createElement('gd:phoneNumber', internationalizePhonenumber($lid->getProperty($telefoon[0])));
					$number->setAttribute('rel', $telefoon[1]);
					$entry->appendChild($number);
				}
			}
			
			if($lid->getGeboortedatum()!=''){
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
			
			$group=$doc->createElement('gContact:groupMembershipInfo');
			$group->setAttribute('href', $this->getGroupId());
			$entry->appendChild($group);
			
			try{
				//echo $doc->saveXML();
				$entryResult = $this->gdata->insertEntry($doc->saveXML(), 'http://www.google.com/m8/feeds/contacts/default/full');
				$photolink=$entryResult->getLink('http://schemas.google.com/contacts/2008/rel#photo')->getHref();
				$this->putPhoto($photolink, PICS_PATH.'/'.$lid->getPasfotoPath());
				
				return true;
			}catch(Exception $e){
				echo 'fout: '.$e->getMessage();
			}
			
			
		}
	}

}
