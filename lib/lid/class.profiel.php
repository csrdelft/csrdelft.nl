<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.lid.php
# -------------------------------------------------------------------
# Houdt de ledenlijst bij.
# -------------------------------------------------------------------


require_once 'class.ldap.php';
require_once 'class.formulier.php';

class Profiel{
	private $lid;
	private $bewerktLid;

	//als dit false is geven we geen loginvelden weer (wachtwoord && gebruikersnaam)
	private $showLoginfields=true;
	
	private $form=array();
	public function __construct($uid, $actie='bewerken'){
		$this->lid=LidCache::getLid($uid);
		$this->bewerktLid=clone $this->lid;

		if($actie=='novietBewerken'){
			$this->hideLoginfields();
		}
		$this->assignFields();
	}

	public function save(){
		foreach($this->getFields() as $field){
			if($field instanceof FormField){
				//als een wachtwoordveld leeg is doen we er niets mee
				if($field instanceof PassField AND $field->getValue()==''){ continue; }
				//is het wel een wijziging?
				if($field->getValue()!=$this->lid->getProperty($field->getName())){
					$this->bewerktLid->setProperty($field->getName(), $field->getValue());
				}	
			}
		}
		if(count($this->diff())>0){
			$this->bewerktLid->logChange($this->ubbDiff());
		}
		return $this->bewerktLid->save() AND $this->bewerktLid->save_ldap();
	}
	
	public function magBewerken(){
		if(LoginLid::instance()->hasPermission('P_LEDEN_MOD')){
			return true;
		}
		if(LoginLid::instance()->hasPermission('P_OUDLEDEN_MOD') AND $this->lid->getStatus()=='S_OUDLID'){
			return true;
		}
		if(LoginLid::instance()->isSelf($this->lid->getUid())){
			return true;
		}
		return false;		
	}

	public function diff(){
		$diff=array();
		$bewerktProfiel=$this->bewerktLid->getProfiel();
		foreach($this->lid->getProfiel() as $veld => $waarde){
			if($waarde!=$bewerktProfiel[$veld]){
				if($veld=='password'){ continue; }
				$diff[$veld]=array('oud' => $waarde, 'nieuw' => $bewerktProfiel[$veld]);
			}
		}
		return $diff;
	}
	public function ubbDiff(){
		$return='Bewerking van [lid='.LoginLid::instance()->getUid().'] op [reldate]'.getDatetime().'[/reldate][br]';
		foreach($this->diff() as $veld => $diff){
			$return.='('.$veld.') '.$diff['oud'].' => '.$diff['nieuw'].'[br]';
		}
		return $return.'[hr]';
	}

	public function getUid(){
		return $this->getLid()->getUid();
	}
	public function getLid(){
		return $this->lid;
	}

	public function hideLoginfields(){
		$this->showLoginfields=false;
	}
	
	public function isPosted(){
		$posted=false;
		foreach($this->form as $field){
			if($field instanceof FormField AND $field->isPosted()){
				$posted=true;
			}
		}
		return $posted;
	}
	public function valid(){
		$valid=true;
		foreach($this->form as $field){
			//we checken alleen de formfields, niet de comments enzo.
			if($field instanceof FormField AND !$field->valid($this->getLid())){
				$valid=false;
			}
		}
		return $valid;
	}
	public function getCurrent($key){
		if(!$this->lid->hasProperty($key)){
			throw new Exception($key.' niet aanwezig in profiel');
		}
		return $this->lid->getProperty($key);
	}
	public function assignFields(){
		LidCache::updateLid($this->lid->getUid());

		$profiel=$this->lid->getProfiel();

		$hasLedenMod=LoginLid::instance()->hasPermission('P_LEDEN_MOD');
		
		//zaken bewerken als we oudlid zijn of P_LEDEN_MOD hebben
		if($profiel['status']=='S_OUDLID' OR $hasLedenMod){
			$form[]=new Comment('Identiteit:');
			$form[]=new RequiredInputField('voornaam', $profiel['voornaam'], 'Voornaam', 50);
			$form[]=new RequiredInputField('voorletters', $profiel['voorletters'], 'Voorletters', 10);
			$form[]=new InputField('tussenvoegsel', $profiel['tussenvoegsel'], 'Tussenvoegsel', 15);
			$form[]=new RequiredInputField('achternaam', $profiel['achternaam'], 'Achternaam', 50);
			if($hasLedenMod){
				$form[]=new InputField('postfix', $profiel['postfix'], 'Postfix', 7);
				$form[]=new SelectField('geslacht', $profiel['geslacht'], 'Geslacht', array('m'=> 'Man', 'v'=>'Vrouw'));
				$form[]=new InputField('voornamen', $profiel['voornamen'], 'Voornamen', 100);
			}
			$form[]=new DatumField('gebdatum', $profiel['gebdatum'], 'Geboortedatum', date('Y')-15);
		}
		
		$form[]=new Comment('Adres:');
		$form[]=new RequiredInputField('adres', $profiel['adres'], 'Straatnaam', 100);
		$form[]=new RequiredInputField('postcode', $profiel['postcode'], 'Postcode', 20);
		$form[]=new RequiredInputField('woonplaats', $profiel['woonplaats'], 'Woonplaats', 50);
		$land=new RequiredInputField('land', $profiel['land'], 'Land', 50);
		$land->setSuggestions('Nederland', 'België', 'Duitsland', 'Frankrijk', 'Verenigd Koninkrijk', 'Verenigde Staten');
		$form[]=$land;
		$form[]=new TelefoonField('telefoon', $profiel['telefoon'], 'Telefoonnummer', 20);
		$form[]=new TelefoonField('mobiel', $profiel['mobiel'], 'Paupernummer', 20);

		if($profiel['status']!='S_OUDLID'){
			$form[]=new Comment('Adres ouders:');
			$form[]=new InputField('o_adres', $profiel['o_adres'], 'Straatnaam', 100);
			$form[]=new InputField('o_postcode', $profiel['o_postcode'], 'Postcode', 20);
			$form[]=new InputField('o_woonplaats', $profiel['o_woonplaats'], 'Woonplaats', 50);
			$land=new InputField('o_land', $profiel['o_land'], 'Land', 50);
			$land->setSuggestions('Nederland', 'België', 'Duitsland', 'Frankrijk', 'Verenigd Koninkrijk', 'Verenigde Staten');
			$form[]=$land;
			$form[]=new TelefoonField('o_telefoon', $profiel['o_telefoon'], 'Telefoonnummer', 20);
		}
			
		$form[]=new Comment('Contact:');
		$email=new RequiredEmailField('email', $profiel['email'], 'Emailadres');
		if(LoginLid::instance()->isSelf($this->lid->getUid())){
			//als we ons eigen profiel bewerken is het email-adres verplicht
			$email->notnull=true;
		}
		$form[]=$email;
		$form[]=new EmailField('msn', $profiel['msn'], 'MSN');
		$form[]=new InputField('icq', $profiel['icq'], 'ICQ', 10); //TODO specifiek ding voor maken
		$form[]=new EmailField('jid', $profiel['jid'], 'Jabber/Google-talk'); //TODO specifiek ding voor maken
		$form[]=new InputField('skype', $profiel['skype'], 'Skype', 20); //TODO specifiek ding voor maken
		$form[]=new UrlField('website', $profiel['website'], 'Website');
		$form[]=new InputField('bankrekening', $profiel['bankrekening'], 'Bankrekening', 11); //TODO specifiek ding voor maken

		if($profiel['status']=='S_OUDLID' OR $hasLedenMod){
			if($profiel['status']=='S_OUDLID'){
				$beginjaar=1950;
			}else{
				$beginjaar=date('Y')-20;
			}
			$form[]=new Comment('Studie en Civitas:');
			$form[]=new InputField('studie', $profiel['studie'], 'Studie', 100);
			$form[]=new IntField('studiejaar', $profiel['studiejaar'], 'Beginjaar studie',date('Y'), $beginjaar);
			if($profiel['status']!='S_OUDLID'){
				$form[]=new InputField('studienr', $profiel['studienr'], 'Studienummer (TU)', 20);
			}
			$form[]=new InputField('beroep', $profiel['beroep'], 'Beroep/werk', 4096);
			$form[]=new IntField('lidjaar', $profiel['lidjaar'], 'Lid sinds', date('Y'), $beginjaar);
			if($profiel['status']=='S_OUDLID'){
				$form[]=new DatumField('lidafdatum', $profiel['lidafdatum'], 'Oudlid sinds');
				$form[]=new SelectField('ontvangtcontactueel', $profiel['ontvangtcontactueel'], 'Ontvangt Contactueel', array('ja'=> 'Ja', 'nee' => 'Nee'));
			}
			
		}
		if($hasLedenMod){
			$form[]=new SelectField('moot', $profiel['moot'], 'Moot', range(0,4));
			$form[]=new SelectField('kring', $profiel['kring'], 'Kring', range(0,9));
			if($this->lid->isLid() OR $profiel['status']=='S_KRINGEL'){
				$form[]=new SelectField('kringleider', $profiel['kringleider'], 'Kringleider', array('n' => 'Nee','o' => 'Ouderejaarskring','e' => 'Eerstejaarskring'));
				$form[]=new SelectField('motebal', $profiel['motebal'], 'Motebal',array('0' => 'Nee','1' => 'Ja'));
			}
			$form[]=new InputField('eetwens', $profiel['eetwens'], 'Dieet', 200);
			
		}
		if($hasLedenMod){
			$form[]=new Comment('Overig');
			//wellicht binnenkort voor iedereen beschikbaar?
			$form[]=new InputField('kerk', $profiel['kerk'], 'Kerk', 50);
			$form[]=new InputField('muziek', $profiel['muziek'], 'Muziekinstrument', 50);
		}

		if($this->showLoginfields===true){
			$form[]=new Comment('Inloggen:');
			$form[]=new NickField('nickname', $profiel['nickname'], 'Bijnaam (inloggen)');

			$form[]=new PassField('password');
		}
		$this->form=$form;
	}
	
	public function getFields(){ return $this->form; }

	public static function resetWachtwoord($uid){
		if(!Lid::exists($uid)){ return false; }
		$lid=LidCache::getLid($uid);
		
		$password=substr(md5(time()), 0, 8);
		$passwordhash=makepasswd($password);

		$sNieuwWachtwoord="UPDATE lid SET password='".$passwordhash."' WHERE uid='".$uid."' LIMIT 1;";
		
		$mail="
Hallo ".$lid->getNaam().",

U heeft een nieuw wachtwoord aangevraagd voor http://csrdelft.nl. U kunt nu inloggen met de volgende combinatie:

".$uid."
".$password."

U kunt uw wachtwoord wijzigen in uw profiel: http://csrdelft.nl/communicatie/profiel/".$uid." .

Met vriendelijke groet,

Namens de PubCie,

".LoginLid::instance()->getLid()->getNaam()."

P.S.: Mocht u nog vragen hebben, dan kan u natuurlijk altijd e-posts sturen naar pubcie@csrdelft.nl";
		return MySql::instance()->query($sNieuwWachtwoord) AND mail($lid->getEmail(), 'Nieuw wachtwoord voor de C.S.R.-stek', $mail, "Bcc: pubcie@csrdelft.nl");

	}
}

?>
