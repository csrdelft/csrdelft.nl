<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.lid.php
# -------------------------------------------------------------------
# Houdt de ledenlijst bij.
# -------------------------------------------------------------------


require_once 'ldap.class.php';
require_once 'formulier.class.php';

class Profiel{
	private $lid;
	private $bewerktLid;

	//zijn we een nieuwe noviet aan het toevoegen?
	private $editNoviet=false;

	private $form=array();
	public function __construct($uid, $actie='bewerken'){
		$this->lid=LidCache::getLid($uid);
		$this->bewerktLid=clone $this->lid;

		if($actie=='novietBewerken'){
			$this->editNoviet=true;
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
		if($this->bewerktLid->save()){
			try{
				$this->bewerktLid->save_ldap();
			}catch(Exception $e){
				//todo: loggen dat LDAP niet beschikbaar is in een mooi eventlog wat ook nog gemaakt moet worden...
			}
			return true;
		}
		return false;
	}

	public function magBewerken(){
		//lid-moderator
		if(LoginLid::instance()->hasPermission('P_LEDEN_MOD')){
			return true;
		}
		//oudlid-moderator
		if(LoginLid::instance()->hasPermission('P_OUDLEDEN_MOD') AND in_array($this->lid->getStatus(), array('S_OUDLID', 'S_ERELID'))){
			return true;
		}
		//novietenbewerker (de novCie dus)
		if($this->editNoviet==true AND LoginLid::instance()->hasPermission('groep:novcie')){
			return true;
		}
		//of het gaat om ons eigen profiel.
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
		//alle veldjes langslopen, en kijken of ze valideren.
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
	/*
	 * Alle profielvelden die bewerkt kunnen worden hier defenieren.
	 * Als we ze hier toevoegen, dan verschijnen ze ook automagisch in het profiel-bewerkding,
	 * en ze worden gecontroleerd met de eigen valideerfuncties.
	 */
	public function assignFields(){
		LidCache::updateLid($this->lid->getUid());

		$profiel=$this->lid->getProfiel();

		$hasLedenMod=LoginLid::instance()->hasPermission('P_LEDEN_MOD');

		//zaken bewerken als we oudlid zijn of P_LEDEN_MOD hebben
		if(in_array($profiel['status'], array('S_OUDLID', 'S_ERELID')) OR $hasLedenMod OR $this->editNoviet){
			$form[]=new Comment('Identiteit:');
			$form[]=new RequiredInputField('voornaam', $profiel['voornaam'], 'Voornaam', 50);
			$form[]=new RequiredInputField('voorletters', $profiel['voorletters'], 'Voorletters', 10);
			$form[]=new InputField('tussenvoegsel', $profiel['tussenvoegsel'], 'Tussenvoegsel', 15);
			$form[]=new RequiredInputField('achternaam', $profiel['achternaam'], 'Achternaam', 50);
			if($hasLedenMod OR $this->editNoviet){
				if(!$this->editNoviet){
					$form[]=new InputField('postfix', $profiel['postfix'], 'Postfix', 7);
				}
				$form[]=new GeslachtField('geslacht', $profiel['geslacht'], 'Geslacht');
				$form[]=new InputField('voornamen', $profiel['voornamen'], 'Voornamen', 100);
			}
			$form[]=new DatumField('gebdatum', $profiel['gebdatum'], 'Geboortedatum', date('Y')-15);
			if(in_array($profiel['status'], array('S_NOBODY', 'S_OVERLEDEN'))){
				$form[]=new DatumField('sterfdatum', $profiel['sterfdatum'], 'Overleden op:');
			}
			if($hasLedenMod OR in_array($profiel['status'], array('S_OUDLID', 'S_ERELID'))){
				$form[]=new UidField('echtgenoot', $profiel['echtgenoot'], 'Echtgenoot (lidnummer):');
				$form[]=new InputField('adresseringechtpaar',$profiel['adresseringechtpaar'], 'Tenaamstelling post echtpaar:',250);
			}
		}

		$form[]=new Comment('Adres:');
		$form[]=new RequiredInputField('adres', $profiel['adres'], 'Straatnaam', 100);
		$form[]=new RequiredInputField('postcode', $profiel['postcode'], 'Postcode', 20);
		$form[]=new RequiredInputField('woonplaats', $profiel['woonplaats'], 'Woonplaats', 50);
		$form[]=new RequiredLandField('land', $profiel['land'], 'Land', 50);
		$form[]=new TelefoonField('telefoon', $profiel['telefoon'], 'Telefoonnummer (vast)', 20);
		$form[]=new TelefoonField('mobiel', $profiel['mobiel'], 'Paupernummer', 20);

		if(in_array($profiel['status'], array('S_OUDLID', 'S_ERELID'))){
			$form[]=new Comment('Adres ouders:');
			$form[]=new InputField('o_adres', $profiel['o_adres'], 'Straatnaam', 100);
			$form[]=new InputField('o_postcode', $profiel['o_postcode'], 'Postcode', 20);
			$form[]=new InputField('o_woonplaats', $profiel['o_woonplaats'], 'Woonplaats', 50);
			$form[]=new LandField('o_land', $profiel['o_land'], 'Land', 50);
			$form[]=new TelefoonField('o_telefoon', $profiel['o_telefoon'], 'Telefoonnummer', 20);
		}

		$form[]=new Comment('Contact:');
		$email=new RequiredEmailField('email', $profiel['email'], 'Emailadres');
		if(LoginLid::instance()->isSelf($this->lid->getUid())){
			//als we ons *eigen* profiel bewerken is het email-adres verplicht
			$email->notnull=true;
		}
		$form[]=$email;
		$form[]=new EmailField('msn', $profiel['msn'], 'MSN');
		$form[]=new InputField('icq', $profiel['icq'], 'ICQ', 10); //TODO specifiek ding voor maken
		$form[]=new EmailField('jid', $profiel['jid'], 'Jabber/Google-talk'); //TODO specifiek ding voor maken
		$form[]=new InputField('skype', $profiel['skype'], 'Skype', 20); //TODO specifiek ding voor maken
		$form[]=new UrlField('linkedin', $profiel['linkedin'], 'Publiek LinkedIn-profiel');
		$form[]=new UrlField('website', $profiel['website'], 'Website');
		$form[]=new InputField('bankrekening', $profiel['bankrekening'], 'Bankrekening', 11); //TODO specifiek ding voor maken
		if($hasLedenMod){
			$form[]=new SelectField('machtiging', $profiel['machtiging'], 'Machtiging getekend?', array('ja'=> 'Ja', 'nee' => 'Nee'));
		}

		if(in_array($profiel['status'], array('S_OUDLID', 'S_ERELID', 'S_NOBODY', 'S_OVERLEDEN', 'S_CIE')) OR $this->lid->getUid()=='6601'){ //vd Wekken mag wel eerder begonnen zijn.
			$beginjaar=1950;
		}else{
			$beginjaar=date('Y')-20;
		}

		if(in_array($profiel['status'], array('S_OUDLID', 'S_ERELID')) OR $hasLedenMod OR $this->editNoviet){
			$form[]=new Comment('Studie:');
		}
		$form[]=new StudieField('studie', $profiel['studie'], 'Studie');
		if(in_array($profiel['status'], array('S_OUDLID', 'S_ERELID')) OR $hasLedenMod OR $this->editNoviet){
			$form[]=new IntField('studiejaar', $profiel['studiejaar'], 'Beginjaar studie', date('Y'), $beginjaar);
		}

		if(!in_array($profiel['status'], array('S_OUDLID', 'S_ERELID'))){
			$form[]=new InputField('studienr', $profiel['studienr'], 'Studienummer (TU)', 20);
		}

		if(!$this->editNoviet AND (in_array($profiel['status'], array('S_OUDLID', 'S_ERELID')) OR $hasLedenMod)){
			$form[]=new InputField('beroep', $profiel['beroep'], 'Beroep/werk', 4096);
			$form[]=new IntField('lidjaar', $profiel['lidjaar'], 'Lid sinds', date('Y'), $beginjaar);
		}

		if(in_array($profiel['status'], array('S_OUDLID', 'S_ERELID', 'S_NOBODY'))){
			$form[]=new DatumField('lidafdatum', $profiel['lidafdatum'], 'Oudlid sinds');
		}
		if(in_array($profiel['status'], array('S_OUDLID', 'S_ERELID')) AND $hasLedenMod){
			$form[]=new SelectField('ontvangtcontactueel', $profiel['ontvangtcontactueel'], 'Ontvangt Contactueel', array('ja'=> 'Ja', 'nee' => 'Nee'));
		}

		if($hasLedenMod AND !$this->editNoviet){
			$form[]=new VerticaleField('verticale', $profiel['verticale'], 'Verticale');
			$form[]=new SelectField('kring', $profiel['kring'], 'Kring', range(0,9));
			if($this->lid->isLid()){
				$form[]=new SelectField('kringleider', $profiel['kringleider'], 'Kringleider', array('n' => 'Nee','o' => 'Ouderejaarskring','e' => 'Eerstejaarskring'));
				$form[]=new SelectField('motebal', $profiel['motebal'], 'Verticaan', array('0' => 'Nee','1' => 'Ja'));
			}
			$form[]=new UidField('patroon', $profiel['patroon'], 'Patroon');
		}

		if($hasLedenMod OR $this->editNoviet){
			$form[]=new Comment('Persoonlijk:');
			$form[]=new InputField('eetwens', $profiel['eetwens'], 'Dieet', 200);
			//wellicht binnenkort voor iedereen beschikbaar?
			$form[]=new InputField('kerk', $profiel['kerk'], 'Kerk', 50);
			$form[]=new InputField('muziek', $profiel['muziek'], 'Muziekinstrument', 50);
		}

		if(LoginLid::instance()->hasPermission('P_ADMIN,P_BESTUUR,groep:novcie')){
			$form[]=new SelectField('ovkaart', $profiel['ovkaart'], 'OV-kaart', array('' => 'Kies...','geen' => '(Nog) geen OV-kaart','week' => 'Week','weekend' => 'Weekend','niet' => 'Niet geactiveerd'));
			$form[]=new SelectField('zingen', $profiel['zingen'], 'Zingen', array('' => 'Kies...','ja' => 'Ja, ik zing in een band/koor','nee' => 'Nee, ik houd niet van zingen','soms' => 'Alleen onder de douche','anders' => 'Anders'));
			$form[]=new TextField('novitiaat', $profiel['novitiaat'], 'Wat verwacht je van het novitiaat?');
			$form[]=new Comment('<br>Einde vragenlijst<br><br><br><br><br>');
			$form[]=new TextField('kgb', $profiel['kgb'], 'NovCie-opmerking');
		}

		if(!$this->editNoviet){
			//we voeren nog geen wachtwoord of bijnaam in bij novieten, die krijgen ze pas na het novitiaat
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
		return
			MySql::instance()->query($sNieuwWachtwoord) AND
			LidCache::flushLid($uid) AND
			$lid->save_ldap() AND
			mail($lid->getEmail(), 'Nieuw wachtwoord voor de C.S.R.-stek', $mail, "From: pubcie@csrdelft.nl\n Bcc: pubcie@csrdelft.nl");

	}
}

?>
