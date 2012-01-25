<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.lid.php
# -------------------------------------------------------------------
# Houdt de ledenlijst bij.
# -------------------------------------------------------------------


require_once 'formulier.class.php';
require_once 'forum/forum.class.php';

class Profiel {
	protected $lid;
	protected $bewerktLid;

	//zijn we een nieuwe noviet aan het toevoegen?
	protected $editNoviet=false;

	protected $form=array();

	public function __construct($lid, $actie='bewerken'){
		if($lid instanceof Lid){
			$this->lid=$lid;
		}else{
			$this->lid=LidCache::getLid($lid);
		}
		$this->bewerktLid=clone $this->lid;

		if($actie=='novietBewerken'){
			$this->editNoviet=true;
		}
	}

	//make al methods from Lid accessible in profiel
	public function __call($name, $arguments){
		if(method_exists($this->lid, $name)){
			return call_user_func_array(array($this->lid, $name), $arguments);
		}else{
			throw new Exception('Call to undefined method Profiel::'.$name);
		}
	}

	public function getRecenteForumberichten(){
		return Forum::getPostsVoorUid($this->lid->getUid());
	}

	private $forumpostcount=-1;
	public function getForumPostCount(){
		if($this->forumpostcount==-1){
			$this->forumpostcount=Forum::getUserPostCount($this->lid->getUid());
		}
		return $this->forumpostcount;
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

	//return an array met contactgegevens.
	public function getContactgegevens(){
		return $this->getProfielFieldsNotEmpty(
			array('email', 'icq', 'msn', 'jid', 'skype', 'linkedin', 'website'));

	}

	public function isPosted(){
		$posted=false;
		foreach($this->getFields() as $field){
			if($field instanceof FormField AND $field->isPosted()){
				$posted=true;
			}
		}
		return $posted;
	}
	public function valid(){
		//alle veldjes langslopen, en kijken of ze valideren.
		$valid=true;
		foreach($this->getFields() as $field){
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
	public function getProfielFieldsNotEmpty($fields){
		$ret=array();
		$profiel=$this->lid->getProfiel();
		foreach($fields as $field){
			if(isset($profiel[$field]) && $profiel[$field]!=''){
				$ret[$field]=$profiel[$field];
			}
		}
		return $ret;
	}
}

class ProfielBewerken extends Profiel {

	public function __construct($lid, $actie){
		parent::__construct($lid, $actie);
		$this->assignFields();
	}
	/*
	 * Alle profielvelden die bewerkt kunnen worden hier definieren.
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
		$form[]=new RequiredInputField('land', $profiel['land'], 'Land', 50);
		$form[]=new TelefoonField('telefoon', $profiel['telefoon'], 'Telefoonnummer (vast)', 20);
		$form[]=new TelefoonField('mobiel', $profiel['mobiel'], 'Paupernummer', 20);

		if(!in_array($profiel['status'], array('S_OUDLID', 'S_ERELID'))){
			$form[]=new Comment('Adres ouders:');
			$form[]=new InputField('o_adres', $profiel['o_adres'], 'Straatnaam', 100);
			$form[]=new InputField('o_postcode', $profiel['o_postcode'], 'Postcode', 20);
			$form[]=new InputField('o_woonplaats', $profiel['o_woonplaats'], 'Woonplaats', 50);
			$form[]=new InputField('o_land', $profiel['o_land'], 'Land', 50);
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
		$form[]=new InputField('studie', $profiel['studie'], 'Studie');
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
			$form[]=new DatumField('lidafdatum', $profiel['lidafdatum'], 'Lid-af sinds');
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
}

class ProfielStatus extends Profiel{

	public function __construct($lid, $actie){
		parent::__construct($lid, $actie);
		$this->assignFields();
	}

	/*
	 * Defineert de velden van formulier voor het wijzigen van lidstatus
	 */
	public function assignFields(){
		LidCache::updateLid($this->lid->getUid());
		$profiel=$this->lid->getProfiel();

		//permissies
		$perm = array('P_LID'=>'Lid', 'P_OUDLID'=>'Oudlid', 'P_NOBODY'=>'Ex-lid', 'P_MAALCIE'=>'MaalCierechten', 'P_BASF'=>'BAS-FCierechten', 'P_ETER'=>'Eter (mag abo\'s) - geen inlog');
		$permbeheer = array('P_BESTUUR'=>'Bestuur', 'P_VAB'=>'Vice-Abactis', 'P_PUBCIE'=>'PubCierechten');
		if(LoginLid::instance()->hasPermission('P_ADMIN')){
			//admin mag alle permissies toekennen
			$perm = array_merge($perm, $permbeheer);
		}elseif(in_array($profiel['permissies'],array('P_BESTUUR', 'P_VAB', 'P_PUBCIE', 'P_MODERATOR'))){
			//niet admin mag geen beheerpermissies aanpassen
			$perm = array($permbeheer[$profiel['permissies']],$permbeheer[$profiel['permissies']]);
		}
		//stati
		$status = array('S_LID'=>'Lid', 'S_GASTLID'=>'Gastlid', 'S_NOVIET'=>'Noviet', 'S_OUDLID'=>'Oudlid', 'S_ERELID'=>'Erelid', 'S_KRINGEL'=>'Kringel', 'S_NOBODY'=>'Ex-lid', 'S_OVERLEDEN'=>'Overleden', 'S_CIE'=>'Commissie & in LDAP adresboek');

		//status-select is eerste veld omdat die bij opslaan als eerste uitgelezen moet worden.
		$form[]=new SelectField('status', $profiel['status'], 'Lidstatus', $status);
		$form[]=new SelectField('permissies', $profiel['permissies'], 'Permissies', $perm);
		$form[]=new DatumField('lidafdatum', $profiel['lidafdatum'], 'Lid-af sinds');
		$form[]=new SelectField('kring', $profiel['kring'], 'Kringnummer', range(0,9));
		$form[]=new InputField('postfix', $profiel['postfix'], 'Postfix', 7);
		$form[]=new SelectField('ontvangtcontactueel', $profiel['ontvangtcontactueel'], 'Ontvangt contactueel?', array('ja'=>'ja','nee'=>'nee'));
		$form[]=new UidField('echtgenoot', $profiel['echtgenoot'], 'Echtgenoot (lidnummer):');
		$form[]=new InputField('adresseringechtpaar',$profiel['adresseringechtpaar'], 'Tenaamstelling post echtpaar:',250);
		$form[]=new DatumField('sterfdatum', $profiel['sterfdatum'], 'Overleden op:');

		$this->form=$form;
	}

	/*
	 * Slaat waardes uit de velden op. Voor opslaan worden sommige velden nog geconditioneerd.
	 * @return bool wel/niet slagen van opslaan van lidgegevens
	 * acties: verwerkt velden, conditioneert die, zet abo's uit, slaat lidgegevens op en mailt fisci.
	 */
	public function save(){
		//relevante gegevens uit velden verwerken
		foreach($this->getFields('formStatus') as $field){
			if($field instanceof FormField){
				//aan de hand van status bepalen welke POSTed velden worden bewaard van het formulier
				if($field->getName()=='status'){
					$keepfields = $this->keepFields($field->getValue());
				}
				//is het wel een wijziging?
				if($keepfields[$field->getName()]['keep']==true){
					if($field->getValue()!=$this->lid->getProperty($field->getName())){
						$this->bewerktLid->setProperty($field->getName(), $field->getValue());
					}
				}else{
					//als het niet bewaard wordt, checken of veld gereset moet worden.
					if($keepfields[$field->getName()]['reset']!==null){
						$this->bewerktLid->setProperty($field->getName(), $keepfields[$field->getName()]['reset']);
					}
				}
			}
		}

		$oudestatus = $this->lid->getProperty('status');
		$status = $this->bewerktLid->getProperty('status');

		//bij niet-admins worden aanpassingen aan permissies ongedaan gemaakt
		if(!LoginLid::instance()->hasPermission('P_ADMIN')){
			if(in_array($perm=$this->lid->getProperty('permissies'), array('P_PUBCIE','P_MODERATOR','P_BESTUUR','P_VAB'))){
				if($perm!=$this->bewerktLid->getProperty('permissies')){
					$this->bewerktLid->setProperty('permissies', $perm);
				}
			}

			//uitzondering: bij aanpassing door een niet-admin automatisch oudlid-permissies instellen voor *hogere* admins bij lid-af maken.
			if(in_array($status, array('S_OUDLID','S_ERELID','S_NOBODY')) 
			AND in_array($this->bewerktLid->getProperty('permissies'), array('P_PUBCIE','P_MODERATOR','P_BESTUUR','P_VAB'))){
				if($status=='S_NOBODY'){
					$st = 'P_NOBODY';
				}else{
					$st = 'P_OUDLID';
				}
				$this->bewerktLid->setProperty('status', $st);
			}
		}
		//maaltijdabo's uitzetten
		$maalabolog = '';
		if(in_array($status,array('S_OUDLID','S_ERELID','S_NOBODY','S_CIE','S_OVERLEDEN')) 
			AND $this->bewerktLid->getProperty('permissies')!='P_ETER'){

			require_once 'maaltijden/maaltrack.class.php';
			$maaltrack = new MaalTrack();
			if($abos = $maaltrack->getAbo($this->lid->getUid())){
				$maalabolog = 'Afmelden abo\'s: ';
				foreach($abos as $abo => $abonaam){
					if($maaltrack->delAbo($abo,$this->lid->getUid())){
						$maalabolog.= $abonaam.' uitgezet. ';
					}else{
						$maalabolog.= $abonaam.' staat nog aan. ';
					}
				}
				$maalabolog.='[br]';
			}
		}

		//changelog, voegt ook log van uitzetten maaltijdabo's toe
		if(count($this->diff())>0){
			$ubbdiff = $this->ubbDiff();
			if($maalabolog){
				$ubbdiff = substr($ubbdiff,0,-4).$maalabolog.'[hr]';
			}
			$this->bewerktLid->logChange($ubbdiff);
		}

		//opslaan
		if($this->bewerktLid->save()){
			try{
				$this->bewerktLid->save_ldap();
			}catch(Exception $e){
				//todo: loggen dat LDAP niet beschikbaar is in een mooi eventlog wat ook nog gemaakt moet worden...
			}

			//mailen naar fisci
			if(in_array($status, array('S_OUDLID','S_ERELID','S_NOBODY','S_OVERLEDEN'))){
				$saldi = '';
				foreach($this->bewerktLid->getSaldi() as $saldo){
					$saldi .= $saldo['naam'].': '.$saldo['saldo']."\n";
				}
$bericht = "Beste fisci,

De lidstatus van ".$this->bewerktLid->getNaamLink('full','plain')." (".$this->bewerktLid->getUid().") is gewijzigd van ".$oudestatus." in ".$status.".

De volgende saldi zijn bekend:
".$saldi."

Met amicale groet,
".LoginLid::instance()->getLid()->getNaamLink('full','plain');
				$from = 'pubcie@csrdelft.nl,vice-abactis@csrdelft.nl';
				$to = 'fiscus@csrdelft.nl,maalcie-fiscus@csrdelft.nl,soccie@csrdelft.nl';
				$bcc = 'pubcie@csrdelft.nl';
				$this->fiscusmailer($from, $to, $bcc, 'Melding lid-af worden', $bericht);
			}

			return true;
		}
		return false;
	}

	/*
	 * Geeft array waarin per veld is bepaald of die bewaard moet worden afhankelijk van de status 
	 * @param $status string lidstatus
	 * @return array($veld=>bool) array met per veld een boolean voor wel/niet bewaren
	 */
	private function keepFields($status){
		$keep = array();
		$keep['status']['keep'] = true;
		$keep['permissies']['keep'] = true;
		
		if(in_array($status, array('S_OUDLID','S_ERELID','S_NOBODY','S_OVERLEDEN'))){
			$toggle = true;
		}else{	
			$toggle = false;
		}
		$keep['postfix']['keep'] = !$toggle;
		$keep['lidafdatum']['keep'] = $toggle;

		if($status=='S_OVERLEDEN'){ $toggle = false; }

		$keep['kring']['keep'] = $toggle;

		if($status=='S_NOBODY'){ $toggle = false; }

		$keep['ontvangtcontactueel']['keep'] = $toggle;
		$keep['echtgenoot']['keep'] = $toggle;
		$keep['adresseringechtpaar']['keep'] = $toggle;

		if($status=='S_OVERLEDEN'){
			$keep['sterfdatum']['keep'] = true;
		}else{
			$keep['sterfdatum']['keep'] = false;
		}
		if(in_array($status, array('S_KRINGEL','S_OVERLEDEN','S_CIE'))){
			$keep['postfix']['keep'] = false;
		}
		if(in_array($status, array('S_LID','S_GASTLID','S_NOVIET','S_KRINGEL'))){
			$keep['kring']['keep'] = true;
		}

		//resetwaardes
		$keep['status']['reset'] = null;
		$keep['permissies']['reset'] = null;
		$keep['lidafdatum']['reset'] = '0000-00-00';
		$keep['postfix']['reset'] = '';
		$keep['ontvangtcontactueel']['reset'] = null;
		$keep['adresseringechtpaar']['reset'] = null;
		$keep['echtgenoot']['reset'] = null;
		$keep['sterfdatum']['reset'] = null;
		$keep['kring']['reset'] = 0;

		return $keep;
	}

	/*
	 * Mailt de fisci een bericht
	 * @param 	$from string of sender(s) email  ('abc@ml.com,de@gf.nl')
	 * 			$to string of receiver(s) email
	 * 			$bcc string of blind receiver(s) email
	 * 			$onderwerp string
	 * 			$bericht string bericht meerregelig gescheiden met \n. Advies is max 70 tekens per regel.
	 * @return bool het mailen is wel/niet succes
	 */
	private function fiscusmailer($from, $to, $bcc, $onderwerp, $bericht){
		$onderwerp = ' =?UTF-8?B?'. base64_encode(htmlspecialchars($onderwerp)) ."?=\n";
		$bericht = htmlspecialchars($bericht);
		$headers = "From: ".$from."\n";
		if($bcc != ''){
			$headers .= "BCC: ".$bcc."\n";
		}
		//content-type en charset zetten zodat rare tekens in wazige griekse namen
		//en euro-tekens correct weergegeven worden in de mails.
		$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
		$headers .= 'X-Mailer: csrdelft.nl/Lidstatuswijzigjetzer'."\n\r";
		return mail($to, $onderwerp, $bericht, $headers);
	}

}
?>
