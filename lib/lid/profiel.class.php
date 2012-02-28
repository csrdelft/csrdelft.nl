<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.lid.php
# -------------------------------------------------------------------
# Houdt de ledenlijst bij.
# -------------------------------------------------------------------


require_once 'formulier.class.php';
require_once 'forum/forum.class.php';

/**
 * Profiel defenieert een stel functies om het aanpassen en weergeven
 * van een profiel te faciliteren.
 * Eigenlijk is het niet meer dan een wrapper om een Lid-object heen met
 * wat extra functionaliteit.
 *
 * In Profiel::$lid wordt het onbewerkte lid opgeslagen, in
 * Profiel::$bewerktLid worden wijzigingen gemaakt. Daardoor kunnen er
 * diffjes van gedraaid worden.
 * 
 */
class Profiel {
	protected $lid;
	protected $bewerktLid;

	//Zijn we een nieuwe noviet aan het toevoegen?
	protected $editNoviet=false;

	//Hierin kan een formulier gedefenieerd worden.
	protected $form=array();

	//we houden voor elke wijziging een changelog bij, die stoppen we
	//bovenin het veld 'changelog' in de database bij het opslaan.
	protected $changelog=array();
	
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

	/**
	 * use php's magic __call-method to make al methods from Lid class
	 * accessible in Profiel
	 */
	public function __call($name, $arguments){
		if(method_exists($this->lid, $name)){
			return call_user_func_array(array($this->lid, $name), $arguments);
		}else{
			throw new Exception('Call to undefined method Profiel::'.$name);
		}
	}

	public function getFormulier(){
		return $this->form;
	}
	public function isPosted(){
		return $this->form->isPosted();
	}
	

	/**
	 * Save bewerktLid en push wijzigingen naar de LDAP.
	 * 
	 */
	public function save(){
		foreach($this->form->getFields() as $field){
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
			$this->bewerktLid->logChange($this->changelog());
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

	/**
	 * Wie mag er allemaal profielen aanpassen?
	 */
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

	/**
	 * Geef een array terug moet de gewijzigde velden.
	 *
	 * @returns
	 * 	array(
	 * 	'veld1' => array(
	 * 		'oud' 	=> oude waarde
	 * 		'nieuw'	=> nieuwe waarde
	 *  ),
	 * 	'veld2' => array( etc...
	 */ 
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

	/**
	 * Maak een stukje ubb-code aan met daarin de huidige wijziging,
	 * door wie en wanneer.
	 *
	 */ 
	public function changelog(){
		$return='[div]';
		foreach($this->changelog as $row){
			if($row!=''){
				$return.=$row.'[br]';
			}
		}
		foreach($this->diff() as $veld => $diff){
			$return.='('.$veld.') '.$diff['oud'].' => '.$diff['nieuw'].'[br]';
		}
		return $return.'[/div][hr]';
	}

	public function getLid(){
		return $this->lid;
	}

	/**
	 * Geef een array met online contactgegevens terug, als de velden niet leeg zijn.
	 */
	public function getContactgegevens(){
		return $this->getNonemptyFields(
			array('email', 'icq', 'msn', 'jid', 'skype', 'linkedin', 'website')
		);
	}

	/**
	 * Geeft de waarde van een bepaald veld in het onbewerkte lid.
	 */
	public function getCurrent($key){
		if(!$this->lid->hasProperty($key)){
			throw new Exception($key.' niet aanwezig in profiel');
		}
		return $this->lid->getProperty($key);
	}

	/**
	 * Reset het wachtwoord van een gebruiker.
	 *  - Stuur een mail naar de gebruiker
	 *  - Wordt niet gelogged in de changelog van het profiel
	 */
	public static function resetWachtwoord($uid){
		if(!Lid::exists($uid)){ return false; }
		$lid=LidCache::getLid($uid);

		$password=substr(md5(time()), 0, 8);
		$passwordhash=makepasswd($password);

		$sNieuwWachtwoord="UPDATE lid SET password='".$passwordhash."' WHERE uid='".$uid."' LIMIT 1;";

		$template=file_get_contents(LIB_PATH.'/templates/profiel/nieuwwachtwoord.mail');
		$values=array(
			'naam' => $lid->getNaam(),
			'uid' => $lid->getUid(),
			'password' => $password,
			'admin_naam' => LoginLid::instance()->getLid()->getNaam());
		
		$mail=new TemplatedMail($lid->getEmail(), 'Nieuw wachtwoord voor de C.S.R.-stek', $template);
		$mail->setBcc("pubcie@csrdelft.nl");
		$mail->setValues($values);
		
		return
			MySql::instance()->query($sNieuwWachtwoord) AND
			LidCache::flushLid($uid) AND
			$lid->save_ldap() AND
			$mail->send();

	}

	/**
	 * Geef een array terug met de velden in het profiel in $fields als
	 * ze niet leeg zijn. Velden krijgen veldnaam als key.
	 */
	public function getNonemptyFields($fields){
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
		$this->createFormulier();
	}


	public function save(){
		$this->changelog[]='Bewerking van [lid='.LoginLid::instance()->getUid().'] op [reldate]'.getDatetime().'[/reldate][br]';

		return parent::save();
	}
	
	/**
	 * Alle profielvelden die bewerkt kunnen worden hier definieren.
	 * Als we ze hier toevoegen, dan verschijnen ze ook automagisch in het profiel-bewerkding,
	 * en ze worden gecontroleerd met de eigen valideerfuncties.
	 */
	public function createFormulier(){
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
		$form[]=new RequiredCountryField('land', $profiel['land'], 'Land');
		$form[]=new TelefoonField('telefoon', $profiel['telefoon'], 'Telefoonnummer (vast)', 20);
		$form[]=new TelefoonField('mobiel', $profiel['mobiel'], 'Paupernummer', 20);

		if(!in_array($profiel['status'], array('S_OUDLID', 'S_ERELID'))){
			$form[]=new Comment('Adres ouders:');
			$form[]=new InputField('o_adres', $profiel['o_adres'], 'Straatnaam', 100);
			$form[]=new InputField('o_postcode', $profiel['o_postcode'], 'Postcode', 20);
			$form[]=new InputField('o_woonplaats', $profiel['o_woonplaats'], 'Woonplaats', 50);
			$form[]=new CountryField('o_land', $profiel['o_land'], 'Land', 50);
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
			$form[]=new JaNeeField('machtiging', $profiel['machtiging'], 'Machtiging getekend?');
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
			$form[]=new DatumField('lidafdatum', $profiel['lidafdatum'], 'Lid-af sinds');
		}
		
		//Bij oudleden, ereleden en overleden personen kan het veld ontvangtcontactueel worden aangepast.
		if(in_array($profiel['status'], array('S_OUDLID', 'S_ERELID', 'S_OVERLEDEN')) AND $hasLedenMod){
			$form[]=new JaNeeField('ontvangtcontactueel', $profiel['ontvangtcontactueel'], 'Ontvangt Contactueel');
		}

		if($hasLedenMod AND !$this->editNoviet){
			$form[]=new VerticaleField('verticale', $profiel['verticale'], 'Verticale');
			$form[]=new SelectField('kring', $profiel['kring'], 'Kring', range(0,9));
			if($this->lid->isLid()){
				$form[]=new SelectField('kringleider', $profiel['kringleider'], 'Kringleider', array('n' => 'Nee','o' => 'Ouderejaarskring', 'e' => 'Eerstejaarskring'));
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
		$form[]=new SubmitButton('opslaan', '<a class="knop" href="/communicatie/profiel/'.$this->getUid().'">Annuleren</a>');
		
		$this->form=new Formulier('/communicatie/profiel/'.$this->getUid().'/bewerken', $form);
		
		$this->form->cssID='profielForm';
	}
	
	/**
	 * We defenieren een valid-functie voor deze profieleditpagina.
	 * De velden die we gebruiken willen graag een lid hebben om bepaalde
	 * dingen te controleren, dus die geven we mee.
	 */
	public function valid(){
		return $this->form->valid($this->lid);
	}
}

/**
 * ProfielStatus is een alternatieve bewerkpagina voor profielen.
 * Daarmee kunnen leden van status wisselen, en worden bijbehorende
 * relevante wijzigingen voorgesteld (abo's uitzetten etc.).
 */
class ProfielStatus extends Profiel{

	public function __construct($lid, $actie){
		parent::__construct($lid, $actie);
		$this->createFormulier();
	}

	/*
	 * Defineert de velden van formulier voor het wijzigen van lidstatus
	 */
	public function createFormulier(){
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
		//@@Uitslag: waarom niet gebruik maken van Lid::getStatusDescription()
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
		$form[]=new SubmitButton();
		$this->form=new Formulier('/communicatie/profiel/'.$this->getUid().'/wijzigStatus/', $form);

		$this->form->cssID='statusForm';

		
	}

	/*
	 * Slaat waardes uit de velden op. Voor opslaan worden sommige velden nog geconditioneerd.
	 * @return bool wel/niet slagen van opslaan van lidgegevens
	 * acties: verwerkt velden, conditioneert die, zet abo's uit, slaat lidgegevens op en mailt fisci.
	 */
	public function save(){
		$this->changelog[]='Statusverandering van [lid='.LoginLid::instance()->getUid().'] op [reldate]'.getDatetime().'[/reldate][br]';


		//aan de hand van status bepalen welke POSTed velden worden opgeslagen van het formulier
		$fieldsToSave = $this->getFieldsToSave($this->form->findByName('status'));
		
		
		
		//relevante gegevens uit velden verwerken
		foreach($this->form->getFields() as $field){
			if($field instanceof FormField){
				
				//mag het opgeslagen worden en is het wel een wijziging?
				if($fieldsToSave[$field->getName()]['save']==true){
					if($field->getValue()!=$this->lid->getProperty($field->getName())){
						$this->bewerktLid->setProperty($field->getName(), $field->getValue());
					}
				}else{
					//als het niet bewaard wordt, checken of veld gereset moet worden.
					if($fieldsToSave[$field->getName()]['reset']!==null){
						$this->bewerktLid->setProperty($field->getName(), $fieldsToSave[$field->getName()]['reset']);
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
		$geenabovoor=array('S_OUDLID','S_ERELID','S_NOBODY','S_CIE','S_OVERLEDEN');
		if(in_array($status, $geenabovoor) AND $this->bewerktLid->getProperty('permissies')!='P_ETER'){
			$this->changelog[]=$this->disableMaaltijdabos();
		}

		
		//hop, saven met die hap
		if(parent::save()){
			//mailen naar fisci...
			if(in_array($status, array('S_OUDLID','S_ERELID','S_NOBODY','S_OVERLEDEN'))){
				$this->notifyFisci();
			}
			return true;
		}
		return false;
	}

	private function disableMaaltijdabos(){
		$return='';
	
		require_once 'maaltijden/maaltrack.class.php';
		$maaltrack = new MaalTrack();
		if($abos = $maaltrack->getAbo($this->lid->getUid())){
			$return = 'Afmelden abo\'s: ';
			foreach($abos as $abo => $abonaam){
				if($maaltrack->delAbo($abo, $this->lid->getUid())){
					$maalabolog.= $abonaam.' uitgezet. ';
				}else{
					$return.= $abonaam.' staat nog aan. ';
				}
			}
			$return.='[br]';
		}
		return $return;
	}

	//@@Uitslag: beetje mosterd na de maaltijd, dat moet natuurlijk voor decharge duidelijk zijn...
	private function notifyFisci(){
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
		
		$to='fiscus@csrdelft.nl,maalcie-fiscus@csrdelft.nl,soccie@csrdelft.nl';

		$mail=new Mail($to, 'Melding lid-af worden', $bericht);
		$mail->setBcc('pubcie@csrdelft.nl');

		return $mail->send();
	}
	/*
	 * Geeft array met per veld afhankelijk van status een boolean voor wel/niet bewaren en een resetwaarde.
	 * 
	 * @param $status string lidstatus
	 * @return array met per veld array met de entries 
	 * 		'save': boolean voor wel/niet opslaan van gePOSTe waarde 
	 * 		'reset': mixed waarde in te vullen bij reset (null is nooit resetten)
	 */
	private function getFieldsToSave($status){
		//true/false is wel/niet bewaren van gePOSTe veldwaarde
		$return = array();
		$return['status']['save'] = true;
		$return['permissies']['save'] = true;
		
		if(in_array($status, array('S_OUDLID','S_ERELID','S_NOBODY','S_OVERLEDEN'))){
			$toggle = true;
		}else{	
			$toggle = false;
		}
		$return['postfix']['save'] = !$toggle;
		$return['lidafdatum']['save'] = $toggle;

		if($status=='S_OVERLEDEN'){ $toggle = false; }

		$return['kring']['save'] = $toggle;

		if($status=='S_NOBODY'){ $toggle = false; }

		$return['ontvangtcontactueel']['save'] = $toggle;
		$return['echtgenoot']['save'] = $toggle;
		$return['adresseringechtpaar']['save'] = $toggle;

		if($status=='S_OVERLEDEN'){
			$return['sterfdatum']['save'] = true;
		}else{
			$return['sterfdatum']['save'] = false;
		}
		if(in_array($status, array('S_KRINGEL','S_OVERLEDEN','S_CIE'))){
			$return['postfix']['save'] = false;
		}
		if(in_array($status, array('S_LID','S_GASTLID','S_NOVIET','S_KRINGEL'))){
			$return['kring']['save'] = true;
		}

		//waardes die ingevuld worden bij een reset (null = nooit resetten)
		$return['status']['reset'] 				= null;
		$return['permissies']['reset'] 			= null;
		$return['lidafdatum']['reset'] 			= '0000-00-00';
		$return['postfix']['reset'] 			= '';
		$return['ontvangtcontactueel']['reset'] = null;
		$return['adresseringechtpaar']['reset'] = null;
		$return['echtgenoot']['reset'] 			= null;
		$return['sterfdatum']['reset'] 			= null;
		$return['kring']['reset'] 				= 0;

		return $return;
	}
}
?>
