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

	private $form=array();
	public function __construct($uid){
		$this->lid=LidCache::getLid($uid);
		$this->bewerktLid=clone $this->lid;

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
	public function getLid(){
		return $this->lid;
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
		$profiel=$this->lid->getProfiel();

		$hasLedenMod=LoginLid::instance()->hasPermission('P_LEDEN_MOD');

		//zaken bewerken als we oudlid zijn of P_LEDEN_MOD hebben
		if($profiel['status']=='S_OUDLID' OR $hasLedenMod){
			$form[]=new Comment('Identiteit:');
			$form[]=new RequiredInputField('voornaam', $profiel['voornaam'], 'Voornaam', 100);
			//TODO: voeg ook voorletters toe aan het profiel
			$form[]=new InputField('tussenvoegsel', $profiel['tussenvoegsel'], 'Tussenvoegsel', 100);
			$form[]=new RequiredInputField('achternaam', $profiel['achternaam'], 'Achternaam', 100);
			if($hasLedenMod){
				$form[]=new InputField('postfix', $profiel['postfix'], 'Postfix', 15);
				$form[]=new SelectField('geslacht', $profiel['geslacht'], 'Geslacht', array('m'=> 'Man', 'v'=>'Vrouw'));
				$form[]=new InputField('voornamen', $profiel['voornamen'], 'Voornamen', 100);
			}
			$form[]=new DatumField('gebdatum', $profiel['gebdatum'], 'Geboortedatum', date('Y')-15);
		}
		
		$form[]=new Comment('Adres:');
		$form[]=new InputField('adres', $profiel['adres'], 'Straatnaam', 100);
		$form[]=new InputField('postcode', $profiel['postcode'], 'Postcode', 20);
		$form[]=new InputField('woonplaats', $profiel['woonplaats'], 'Woonplaats', 50);
		$land=new InputField('land', $profiel['land'], 'Land', 50);
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
		$email=new EmailField('email', $profiel['email'], 'Emailadres');
		if(LoginLid::instance()->isSelf($this->lid->getUid())){
			//als we ons eigen profiel bewerken is het email-adres verplicht
			$email->notnull=true;
		}
		$form[]=$email;
		$form[]=new EmailField('msn', $profiel['msn'], 'MSN');
		$form[]=new InputField('icq', $profiel['icq'], 'ICQ', 20); //TODO specifiek ding voor maken
		$form[]=new EmailField('jid', $profiel['jid'], 'Jabber/Google-talk'); //TODO specifiek ding voor maken
		$form[]=new InputField('skype', $profiel['skype'], 'Skype', 20); //TODO specifiek ding voor maken
		$form[]=new UrlField('website', $profiel['website'], 'Website');
		$form[]=new InputField('bankrekening', $profiel['bankrekening'], 'Bankrekening', 20); //TODO specifiek ding voor maken

		if($profiel['status']=='S_OUDLID' OR $hasLedenMod){
			if($profiel['status']=='S_OUDLID'){
				$beginjaar=1950;
			}else{
				$beginjaar=date('Y')-20;
			}
			$form[]=new Comment('Studie en Civitas:');
			$form[]=new InputField('studie', $profiel['studie'], 'Studie', 60);
			$form[]=new IntField('studiejaar', $profiel['studiejaar'], 'Beginjaar studie',date('Y'), $beginjaar);
			if($profiel['status']!='S_OUDLID'){
				$form[]=new InputField('studienr', $profiel['studienr'], 'Studienummer (TU)', 20);
			}
			$form[]=new InputField('beroep', $profiel['beroep'], 'Beroep/werk', 50);
			$form[]=new IntField('lidjaar', $profiel['lidjaar'], 'Lid sinds', date('Y'), $beginjaar);
			if($profiel['status']=='S_OUDLID'){
				$form[]=new DatumField('einddatum', $profiel['einddatum'], 'Oudlid sinds');
			}
		}
		if($hasLedenMod){
			$form[]=new SelectField('moot', $profiel['moot'], 'Moot', range(0,4));
			$form[]=new SelectField('kring', $profiel['kring'], 'Kring', range(0,9));
			$form[]=new SelectField('kringleider', $profiel['kringleider'], 'Kringleider', array('n' => 'Nee','o' => 'Ouderejaarskring','e' => 'Eerstejaarskring'));
			$form[]=new SelectField('motebal', $profiel['motebal'], 'Motebal',array('0' => 'Nee','1' => 'Ja'));
			$form[]=new InputField('eetwens', $profiel['eetwens'], 'Dieet', 20);
		}
		if($hasLedenMod){
			$form[]=new Comment('Overig');
			//wellicht binnenkort voor iedereen beschikbaar?
			$form[]=new InputField('kerk', $profiel['kerk'], 'Kerk', 50);
			$form[]=new InputField('muziek', $profiel['muziek'], 'Muziekinstrument', 50);
		}
		
		$form[]=new Comment('Inloggen:');
		$form[]=new NickField('nickname', $profiel['nickname'], 'Bijnaam (inloggen)');

		$form[]=new PassField('password');
		$this->form=$form;
	}
	
	public function getFields(){ return $this->form; }
}

?>
