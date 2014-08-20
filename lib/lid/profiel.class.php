<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.lid.php
# -------------------------------------------------------------------
# Houdt de ledenlijst bij.
# -------------------------------------------------------------------


require_once 'MVC/model/entity/Mail.class.php';

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

	/** @var  Lid */
	protected $lid;
	/** @var  Lid */
	protected $bewerktLid;
	/** @var Bool Zijn we een nieuwe noviet aan het toevoegen? */
	protected $editNoviet = false;
	/** @var Formulier Hierin kan een formulier gedefinieerd worden. */
	protected $form = null;
	//we houden voor elke wijziging een changelog bij, die stoppen we
	//bovenin het veld 'changelog' in de database bij het opslaan.
	protected $changelog = array();

	public function __construct($lid, $actie = 'bewerken') {
		if ($lid instanceof Lid) {
			$this->lid = $lid;
		} else {
			$this->lid = LidCache::getLid($lid);
		}
		$this->bewerktLid = clone $this->lid;

		if ($actie == 'novietBewerken') {
			$this->editNoviet = true;
		}
	}

	/**
	 * use php's magic __call-method to make al methods from Lid class
	 * accessible in Profiel
	 */
	public function __call($name, $arguments) {
		if (method_exists($this->lid, $name)) {
			return call_user_func_array(array($this->lid, $name), $arguments);
		} else {
			throw new Exception('Call to undefined method Profiel::' . $name);
		}
	}

	public function getFormulier() {
		return $this->form;
	}

	public function isPosted() {
		return $this->form->isPosted();
	}

	/**
	 * Save bewerktLid en push wijzigingen naar de LDAP.
	 * 
	 */
	public function save() {

		if (count($this->diff()) > 0) {
			$this->bewerktLid->logChange($this->changelog());
		}

		if ($this->bewerktLid->save()) {
			try {
				$this->bewerktLid->save_ldap();
			} catch (Exception $e) {
				//todo: loggen dat LDAP niet beschikbaar is in een mooi eventlog wat ook nog gemaakt moet worden...
			}
			return true;
		}
		return false;
	}

	/**
	 * Wie mag er allemaal profielen aanpassen?
	 */
	public function magBewerken() {
		//lid-moderator
		if (LoginModel::mag('P_LEDEN_MOD')) {
			return true;
		}
		//oudlid-moderator
		if (LoginModel::mag('P_LEDEN_MOD') AND in_array($this->lid->getStatus(), array('S_OUDLID', 'S_ERELID'))) {
			return true;
		}
		//novietenbewerker (de novCie dus)
		if ($this->editNoviet == true AND LoginModel::mag('groep:novcie')) {
			return true;
		}
		//of het gaat om ons eigen profiel.
		if (LoginModel::getUid() === $this->lid->getUid()) {
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
	public function diff() {
		$diff = array();
		$bewerktProfiel = $this->bewerktLid->getProfiel();
		foreach ($this->lid->getProfiel() as $veld => $waarde) {
			if ($waarde != $bewerktProfiel[$veld]) {
				if ($veld == 'password') {
					continue;
				}
				$diff[$veld] = array('oud' => $waarde, 'nieuw' => $bewerktProfiel[$veld]);
			}
		}
		return $diff;
	}

	/**
	 * Maak een stukje ubb-code aan met daarin de huidige wijziging,
	 * door wie en wanneer.
	 *
	 */
	public function changelog() {
		$return = '[div]';
		foreach ($this->changelog as $row) {
			if ($row != '') {
				$return.=$row . '[br]';
			}
		}
		foreach ($this->diff() as $veld => $diff) {
			$return .= '(' . $veld . ') ' . $diff['oud'] . ' => ' . $diff['nieuw'] . '[br]';
		}
		return $return . '[/div][hr]';
	}

	public function getLid() {
		return $this->lid;
	}

	/**
	 * Geef een array met online contactgegevens terug, als de velden niet leeg zijn.
	 */
	public function getContactgegevens() {
		return $this->getNonemptyFields(
						array('email', 'icq', 'msn', 'jid', 'skype', 'linkedin', 'website')
		);
	}

	/**
	 * Geeft de waarde van een bepaald veld in het onbewerkte lid.
	 */
	public function getCurrent($key) {
		if (!$this->lid->hasProperty($key)) {
			throw new Exception($key . ' niet aanwezig in profiel');
		}
		return $this->lid->getProperty($key);
	}

	/**
	 * Reset het wachtwoord van een gebruiker.
	 *  - Stuur een mail naar de gebruiker
	 *  - Wordt niet gelogged in de changelog van het profiel
	 */
	public static function resetWachtwoord($uid) {
		if (!Lid::exists($uid)) {
			return false;
		}
		$lid = LidCache::getLid($uid);

		$password = substr(md5(time()), 0, 8);
		$passwordhash = makepasswd($password);
		$sNieuwWachtwoord = "UPDATE lid SET password='" . $passwordhash . "' WHERE uid='" . $uid . "' LIMIT 1;";

		$bericht = file_get_contents(SMARTY_TEMPLATE_DIR . 'MVC/mail/nieuwwachtwoord.mail');
		$values = array(
			'NAAM'	 => $lid->getNaam(),
			'UID'	 => $lid->getUid(),
			'PW'	 => $password,
			'ADMIN'	 => LoginModel::instance()->getLid()->getNaam()
		);
		$mail = new Mail($lid->getEmail(), 'Nieuw wachtwoord voor de C.S.R.-stek', $bericht);
		$mail->addBcc("pubcie@csrdelft.nl");
		$mail->setPlaceholders($values);
		return
				MijnSqli::instance()->query($sNieuwWachtwoord) AND
				LidCache::flushLid($uid) AND
				$lid->save_ldap() AND
				$mail->send();
	}

	/**
	 * Geef een array terug met de velden in het profiel in $fields als
	 * ze niet leeg zijn. Velden krijgen veldnaam als key.
	 */
	public function getNonemptyFields($fields) {
		$ret = array();
		$profiel = $this->lid->getProfiel();
		foreach ($fields as $field) {
			if (isset($profiel[$field]) && $profiel[$field] != '') {
				$ret[$field] = $profiel[$field];
			}
		}
		return $ret;
	}

}

class ProfielBewerken extends Profiel {

	public function __construct($lid, $actie) {
		parent::__construct($lid, $actie);
		$this->createFormulier();
	}

	/**
	 * Alle profielvelden die bewerkt kunnen worden hier definieren.
	 * Als we ze hier toevoegen, dan verschijnen ze ook automagisch in het profiel-bewerkding,
	 * en ze worden gecontroleerd met de eigen valideerfuncties.
	 */
	public function createFormulier() {
		LidCache::updateLid($this->lid->getUid());

		$profiel = $this->lid->getProfiel();

		$hasLedenMod = LoginModel::mag('P_LEDEN_MOD') AND LoginModel::getUid() != '1207';

		if (!$this->editNoviet) {
			//we voeren nog geen wachtwoord of bijnaam in bij novieten, die krijgen ze pas na het novitiaat
			$form[] = new Subkopje('Inloggen:');
			$form[] = new NickField('nickname', $profiel['nickname'], 'Bijnaam', $this->lid);
			if ($hasLedenMod) {
				$form[] = new DuckField('duckname', $profiel['duckname'], 'Duckstad-naam', $this->lid);
			}
			if ($hasLedenMod OR LoginModel::getUid() === '1207') {
				$form[] = new Subkopje('Duck-pasfoto:');
				$path = PICS_PATH . $this->lid->getDuckfotoPath();
				if (strpos($path, '/duck') !== false AND ! endsWith($path, 'eend.jpg')) {
					$duckfoto = new Afbeelding($path);
					$duckfoto->directory = pathinfo($path, PATHINFO_DIRNAME) . '/';
					$duckfoto->filename = pathinfo($path, PATHINFO_BASENAME);
					$duckfoto->filesize = filesize($path);
				} else {
					$duckfoto = null;
				}
				$form[] = new ImageField('duckfoto', $duckfoto, 'duck/', null, null, null, 250);
			}
			$form[] = new WachtwoordWijzigenField('password', $this->lid);
		}

		//zaken bewerken als we oudlid zijn of P_LEDEN_MOD hebben
		if (in_array($profiel['status'], array('S_OUDLID', 'S_ERELID')) OR $hasLedenMod OR $this->editNoviet) {
			$form[] = new Subkopje('Identiteit:');
			$form[] = new RequiredTextField('voornaam', $profiel['voornaam'], 'Voornaam', 50);
			$form[] = new RequiredTextField('voorletters', $profiel['voorletters'], 'Voorletters', 10);
			$form[] = new TextField('tussenvoegsel', $profiel['tussenvoegsel'], 'Tussenvoegsel', 15);
			$form[] = new RequiredTextField('achternaam', $profiel['achternaam'], 'Achternaam', 50);
			if ($hasLedenMod OR $this->editNoviet) {
				if (!$this->editNoviet) {
					$form[] = new TextField('postfix', $profiel['postfix'], 'Postfix', 7);
				}
				$form[] = new GeslachtField('geslacht', $profiel['geslacht'], 'Geslacht');
				$form[] = new TextField('voornamen', $profiel['voornamen'], 'Voornamen', 100);
			}
			$form[] = new DatumField('gebdatum', $profiel['gebdatum'], 'Geboortedatum', date('Y') - 15);
			if (in_array($profiel['status'], array('S_NOBODY', 'S_EXLID', 'S_OVERLEDEN'))) {
				$form[] = new DatumField('sterfdatum', $profiel['sterfdatum'], 'Overleden op:');
			}
			if ($hasLedenMod OR in_array($profiel['status'], array('S_OUDLID', 'S_ERELID', 'S_OVERLEDEN'))) {
				$form[] = new LidField('echtgenoot', $profiel['echtgenoot'], 'Echtgenoot (naam/lidnr):', 'allepersonen');
				$form[] = new Subkopje('Oudledenpost:');
				$form[] = new TextField('adresseringechtpaar', $profiel['adresseringechtpaar'], 'Tenaamstelling post echtpaar:', 250);
				$form[] = new SelectField('ontvangtcontactueel', $profiel['ontvangtcontactueel'], 'Ontvangt Contactueel?', array('ja' => 'ja', 'digitaal' => 'ja, digitaal', 'nee' => 'nee'));
			}
		}

		$form[] = new Subkopje('Adres:');
		$form[] = new RequiredTextField('adres', $profiel['adres'], 'Straatnaam + Huisnummer', 100);
		$form[] = new RequiredTextField('postcode', $profiel['postcode'], 'Postcode', 20);
		$form[] = new RequiredTextField('woonplaats', $profiel['woonplaats'], 'Woonplaats', 50);
		$form[] = new RequiredLandField('land', $profiel['land'], 'Land');
		$form[] = new TelefoonField('telefoon', $profiel['telefoon'], 'Telefoonnummer (vast)', 20);
		$form[] = new TelefoonField('mobiel', $profiel['mobiel'], 'Paupernummer', 20);

		if (!in_array($profiel['status'], array('S_OUDLID', 'S_ERELID'))) {
			$form[] = new Subkopje('Adres ouders:');
			$form[] = new TextField('o_adres', $profiel['o_adres'], 'Straatnaam', 100);
			$form[] = new TextField('o_postcode', $profiel['o_postcode'], 'Postcode', 20);
			$form[] = new TextField('o_woonplaats', $profiel['o_woonplaats'], 'Woonplaats', 50);
			$form[] = new LandField('o_land', $profiel['o_land'], 'Land', 50);
			$form[] = new TelefoonField('o_telefoon', $profiel['o_telefoon'], 'Telefoonnummer', 20);
		}

		$form[] = new Subkopje('Contact:');
		$email = new RequiredEmailField('email', $profiel['email'], 'Emailadres');
		if (LoginModel::getUid() === $this->lid->getUid()) {
			//als we ons *eigen* profiel bewerken is het email-adres verplicht
			$email->not_null = true;
		}
		$form[] = $email;
		$form[] = new EmailField('msn', $profiel['msn'], 'MSN');
		$form[] = new TextField('icq', $profiel['icq'], 'ICQ', 10); //TODO specifiek ding voor maken
		$form[] = new EmailField('jid', $profiel['jid'], 'Jabber/Google-talk'); //TODO specifiek ding voor maken
		$form[] = new TextField('skype', $profiel['skype'], 'Skype', 20); //TODO specifiek ding voor maken
		$form[] = new UrlField('linkedin', $profiel['linkedin'], 'Publiek LinkedIn-profiel');
		$form[] = new UrlField('website', $profiel['website'], 'Website');

		$form[] = new Subkopje('Boekhouding:');
		$form[] = new TextField('bankrekening', $profiel['bankrekening'], 'Bankrekening', 18); //TODO specifiek ding voor maken
		if ($hasLedenMod) {
			$form[] = new JaNeeField('machtiging', $profiel['machtiging'], 'Machtiging getekend?');
		}
		if (LoginModel::mag('P_ADMIN')) {
			$form[] = new IntField('soccieID', $profiel['soccieID'], 'SoccieID (uniek icm. bar)', 0, 10000);
			$form[] = new SelectField('createTerm', $profiel['createTerm'], 'Aangemaakt bij', array('barvoor' => 'barvoor', 'barmidden' => 'barmidden', 'barachter' => 'barachter', 'soccie' => 'soccie'));
		}

		$form[] = new Subkopje('Studie:');
		$form[] = new StudieField('studie', $profiel['studie'], 'Studie');
		$form['studiejaar'] = new IntField('studiejaar', $profiel['studiejaar'], 'Beginjaar studie', 1950, date('Y'));
		$form['studiejaar']->leden_mod = $hasLedenMod;

		if (!in_array($profiel['status'], array('S_OUDLID', 'S_ERELID'))) {
			$form[] = new TextField('studienr', $profiel['studienr'], 'Studienummer (TU)', 20);
		}

		if (!$this->editNoviet AND ( in_array($profiel['status'], array('S_OUDLID', 'S_ERELID')) OR $hasLedenMod)) {
			$form[] = new TextField('beroep', $profiel['beroep'], 'Beroep/werk', 4096);
			$form[] = new IntField('lidjaar', $profiel['lidjaar'], 'Lid sinds', 1950, date('Y'));
		}

		if (in_array($profiel['status'], array('S_OUDLID', 'S_ERELID', 'S_NOBODY', 'S_EXLID'))) {
			$form[] = new DatumField('lidafdatum', $profiel['lidafdatum'], 'Lid-af sinds');
		}

		if ($hasLedenMod AND ! $this->editNoviet) {
			$form[] = new VerticaleField('verticale', $profiel['verticale'], 'Verticale');
			$form[] = new SelectField('kring', $profiel['kring'], 'Kring', range(0, 9));
			if ($this->lid->isLid()) {
				$form[] = new SelectField('kringleider', $profiel['kringleider'], 'Kringleider', array('n' => 'Nee', 'o' => 'Ouderejaarskring', 'e' => 'Eerstejaarskring'));
				$form[] = new SelectField('motebal', $profiel['motebal'], 'Verticaan', array('0' => 'Nee', '1' => 'Ja'));
			}
			$form[] = new LidField('patroon', $profiel['patroon'], 'Patroon', 'allepersonen');
		}

		$form[] = new Subkopje('Persoonlijk:');
		if ($hasLedenMod OR $this->editNoviet) {
			$form[] = new TextField('eetwens', $profiel['eetwens'], 'Dieet/allergie', 200);
			//wellicht binnenkort voor iedereen beschikbaar?
			$form[] = new TextField('kerk', $profiel['kerk'], 'Kerk', 50);
			$form['lengte'] = new IntField('lengte', $profiel['lengte'], 'Lengte (cm)', 50, 250);
			$form['lengte']->leden_mod = true;
			$form[] = new TextField('vrienden', $profiel['vrienden'], 'Vrienden binnnen C.S.R./lichting', 300);
			$form[] = new TextField('middelbareSchool', $profiel['middelbareSchool'], 'Middelbare school', 200);
		}
		$form[] = new TextField('muziek', $profiel['muziek'], 'Muziekinstrument', 50);

		if (LoginModel::mag('P_ADMIN,R_BESTUUR,groep:novcie')) {
			$form[] = new SelectField('ovkaart', $profiel['ovkaart'], 'OV-kaart', array('' => 'Kies...', 'geen' => '(Nog) geen OV-kaart', 'week' => 'Week', 'weekend' => 'Weekend', 'niet' => 'Niet geactiveerd'));
			$form[] = new SelectField('zingen', $profiel['zingen'], 'Zingen', array('' => 'Kies...', 'ja' => 'Ja, ik zing in een band/koor', 'nee' => 'Nee, ik houd niet van zingen', 'soms' => 'Alleen onder de douche', 'anders' => 'Anders'));
			$form[] = new TextareaField('novitiaat', $profiel['novitiaat'], 'Wat verwacht je van het novitiaat?');
			$form[] = new HtmlComment('<br><h3>Einde vragenlijst</h3><br><br><br><br><br>');
			$form[] = new HtmlComment('<div id="novcieKnopFormulier"><h3>In te vullen door NovCie: (klik hier)</h3></div><div id="novcieFormulier">');
			$form[] = new SelectField('novietSoort', $profiel['novietSoort'], 'Soort Noviet', array('noviet', 'nanoviet'));
			$form[] = new SelectField('matrixPlek', $profiel['matrixPlek'], 'Matrix plek', array('voor', 'midden', 'achter'));
			$form[] = new SelectField('startkamp', $profiel['startkamp'], 'Startkamp', array('ja', 'nee'));
			$form[] = new TextareaField('medisch', $profiel['medisch'], 'medisch (NB alleen als relevant voor hele NovCie)');
			$form[] = new TextareaField('novitiaatBijz', $profiel['novitiaatBijz'], 'Bijzonderheden novitiaat (op dag x ...)');
			$form[] = new TextareaField('kgb', $profiel['kgb'], 'Overige NovCie-opmerking');
			$form[] = new HtmlComment('</div>');
		}
		$form[] = new FormButtons('/communicatie/profiel/' . $this->getUid());

		if ($this->editNoviet) {
			$this->form = new Formulier(null, 'profielForm', '/communicatie/profiel/' . $this->getUid() . '/novietBewerken');
			$this->form->addFields($form);
		} else {
			$this->form = new Formulier(null, 'profielForm', '/communicatie/profiel/' . $this->getUid() . '/bewerken');
			$this->form->addFields($form);
		}
	}

	/**
	 * We defenieren een valid-functie voor deze profieleditpagina.
	 * De velden die we gebruiken willen graag een lid hebben om bepaalde
	 * dingen te controleren, dus die hebben we meegegeven bij het aanmaken.
	 */
	public function validate() {
		return $this->form->validate();
	}

	public function save() {
		$this->changelog[] = 'Bewerking van [lid=' . LoginModel::getUid() . '] op [reldate]' . getDatetime() . '[/reldate]';

		foreach ($this->form->getFields() as $field) {
			//duck-pasfoto opslaan
			if ($field instanceof FileField) {
				$path = $field->getModel()->filename;
				$ext = '.' . pathinfo($path, PATHINFO_EXTENSION);
				$field->opslaan(PICS_PATH . 'pasfoto/duck/', $this->getUid() . $ext, true);
				continue;
			}
			if ($field instanceof InputField) {
				//als een wachtwoordveld leeg is doen we er niets mee
				if ($field instanceof WachtwoordWijzigenField AND $field->getValue() == '') {
					continue;
				}
				//is het wel een wijziging?
				if ($field->getValue() != $this->lid->getProperty($field->getName())) {
					$this->bewerktLid->setProperty($field->getName(), $field->getValue());
				}
			}
		}

		return parent::save();
	}

}

/**
 * ProfielStatus is een alternatieve bewerkpagina voor profielen.
 * Daarmee kunnen leden van status wisselen, en worden bijbehorende
 * relevante wijzigingen voorgesteld (abo's uitzetten etc.).
 */
class ProfielStatus extends Profiel {

	public function __construct($lid, $actie) {
		parent::__construct($lid, $actie);
		$this->createFormulier();
	}

	/*
	 * Defineert de velden van formulier voor het wijzigen van lidstatus
	 */

	public function createFormulier() {
		LidCache::updateLid($this->lid->getUid());
		$profiel = $this->lid->getProfiel();

		//permissies
		$perm = array('R_LID' => 'Lid', 'R_OUDLID' => 'Oudlid', 'R_NOBODY' => 'Ex-lid/Nobody', 'R_MAALCIE' => 'MaalCierechten', 'R_BASF' => 'BAS-FCierechten', 'R_ETER' => 'Eter (inlog voor abo\'s)');
		$permbeheer = array('R_BESTUUR' => 'Bestuur', 'R_PUBCIE' => 'PubCierechten');
		if (LoginModel::mag('P_ADMIN')) {
			//admin mag alle permissies toekennen
			$perm = array_merge($perm, $permbeheer);
		} elseif (in_array($profiel['permissies'], array('R_BESTUUR', 'R_PUBCIE'))) {
			//niet admin mag geen beheerpermissies aanpassen
			$perm = array($permbeheer[$profiel['permissies']], $permbeheer[$profiel['permissies']]);
		}

		//stati
		$status = Status::getAllDescriptions();

		//status-select is eerste veld omdat die bij opslaan als eerste uitgelezen moet worden.
		$form[] = new SelectField('status', $profiel['status'], 'Lidstatus', $status);
		$form[] = new SelectField('permissies', $profiel['permissies'], 'Permissies', $perm);
		$form[] = new DatumField('lidafdatum', $profiel['lidafdatum'], 'Lid-af sinds');
		$form[] = new SelectField('kring', $profiel['kring'], 'Kringnummer', range(0, 9));
		$form[] = new TextField('postfix', $profiel['postfix'], 'Postfix', 7);
		$form[] = new SelectField('ontvangtcontactueel', $profiel['ontvangtcontactueel'], 'Ontvangt Contactueel?', array('ja' => 'ja', 'digitaal' => 'ja, digitaal', 'nee' => 'nee'));
		$form[] = new LidField('echtgenoot', $profiel['echtgenoot'], 'Echtgenoot (naam/lidnr):', 'allepersonen');
		$form[] = new TextField('adresseringechtpaar', $profiel['adresseringechtpaar'], 'Tenaamstelling post echtpaar:', 250);
		$form[] = new DatumField('sterfdatum', $profiel['sterfdatum'], 'Overleden op:');
		$form[] = new FormButtons();

		$this->form = new Formulier(null, 'statusForm', '/communicatie/profiel/' . $this->getUid() . '/wijzigstatus/');
		$this->form->addFields($form);
	}

	/**
	 * We defenieren een valid-functie voor deze statuswijzigpagina.
	 * De velden die we gebruiken willen graag een lid hebben om bepaalde
	 * dingen te controleren, dus die geven we mee.
	 */
	public function validate() {
		return $this->form->validate($this->lid);
	}

	/**
	 * Slaat waardes uit de velden op. Voor opslaan worden sommige velden nog geconditioneerd.
	 *
	 * @return bool wel/niet slagen van opslaan van lidgegevens
	 * acties: verwerkt velden, conditioneert die, zet abo's uit, slaat lidgegevens op en mailt fisci.
	 */
	public function save() {
		$this->changelog[] = 'Statusverandering van [lid=' . LoginModel::getUid() . '] op [reldate]' . getDatetime() . '[/reldate]';

		//aan de hand van status bepalen welke POSTed velden worden opgeslagen van het formulier
		$fieldsToSave = $this->getFieldsToSave($this->form->findByName('status')->getValue());

		//relevante gegevens uit velden verwerken
		foreach ($this->form->getFields() as $field) {
			if ($field instanceof InputField) {
				//mag het opgeslagen worden en is het wel een wijziging?
				if ($fieldsToSave[$field->getName()]['save'] == true) {
					if ($field->getValue() != $this->lid->getProperty($field->getName())) {
						$this->bewerktLid->setProperty($field->getName(), $field->getValue());
					}
				} else {
					//als het niet bewaard wordt, checken of veld gereset moet worden.
					if ($fieldsToSave[$field->getName()]['reset'] !== null) {
						$this->bewerktLid->setProperty($field->getName(), $fieldsToSave[$field->getName()]['reset']);
					}
				}
			}
		}

		$oudestatus = $this->lid->getProperty('status');
		$nieuwestatus = $this->bewerktLid->getProperty('status');

		$oudepermissie = $this->lid->getProperty('permissies');
		$nieuwepermissie = $this->bewerktLid->getProperty('permissies');

		//bij wijzigingen door niet-admins worden aanpassingen aan permissies ongedaan gemaakt
		if (!LoginModel::mag('P_ADMIN')) {

			$adminperms = array('R_PUBCIE', 'R_BESTUUR');

			if (in_array($oudepermissie, $adminperms)) {
				if ($oudepermissie != $nieuwepermissie) {
					$this->bewerktLid->setProperty('permissies', $oudepermissie);
					$nieuwepermissie = $this->bewerktLid->getProperty('permissies');
				}
			}

			//uitzondering: bij aanpassing door een niet-admin automatisch oudlid-permissies instellen 
			//voor *hogere* admins bij lid-af maken.
			if (in_array($nieuwestatus, array('S_OUDLID', 'S_ERELID', 'S_EXLID', 'S_NOBODY')) AND in_array($nieuwepermissie, $adminperms)) {
				$nieuwepermissie = Status::getDefaultPermission($nieuwestatus);
				$this->bewerktLid->setProperty('permissies', $nieuwepermissie);
			}
		}

		//maaltijd en corvee bijwerken
		$geenAboEnCorveeVoor = array('S_OUDLID', 'S_ERELID', 'S_NOBODY', 'S_EXLID', 'S_CIE', 'S_OVERLEDEN');
		if (in_array($nieuwestatus, $geenAboEnCorveeVoor)) {
			//maaltijdabo's uitzetten (R_ETER is een S_NOBODY die toch een abo mag hebben)
			if ($nieuwepermissie != 'R_ETER') {
				$this->changelog[] = $this->disableMaaltijdabos();
			}

			//toekomstige corveetaken verwijderen
			$removedcorvee = $this->removeToekomstigeCorvee($oudestatus, $nieuwestatus);
			if ($removedcorvee != '') {
				$this->changelog[] = $removedcorvee;
			}
		}

		//hop, saven met die hap
		if (parent::save()) {
			//mailen naar fisci,bibliothecaris...
			$wordtinactief = array('S_OUDLID', 'S_ERELID', 'S_NOBODY', 'S_EXLID', 'S_OVERLEDEN');
			;
			$wasactief = array('S_NOVIET', 'S_GASTLID', 'S_LID', 'S_KRINGEL');
			if (in_array($nieuwestatus, $wordtinactief) AND in_array($oudestatus, $wasactief)) {
				$this->notifyFisci($oudestatus, $nieuwestatus);

				$this->notifyBibliothecaris($oudestatus, $nieuwestatus);
			}
			return true;
		}
		return false;
	}

	/**
	 * Zet alle abo's uit en geeft een changelog regel terug
	 * 
	 * @return string changelogregel
	 */
	private function disableMaaltijdabos() {
		require_once 'maalcie/model/MaaltijdAbonnementenModel.class.php';
		$aantal = MaaltijdAbonnementenModel::verwijderAbonnementenVoorLid($this->lid->getUid());
		return 'Afmelden abo\'s: ' . $aantal . ' uitgezet. ';
	}

	/**
	 * Verwijder toekomstige corveetaken en geef changelogregel terug
	 *
	 * @param string $oudestatus    voor wijziging
	 * @param string $nieuwestatus  na wijziging
	 * @return string changelogregel
	 */
	private function removeToekomstigeCorvee($oudestatus, $nieuwestatus) {
		$uid = $this->bewerktLid->getUid();
		$taken = CorveeTakenModel::getKomendeTakenVoorLid($uid);
		$aantal = CorveeTakenModel::verwijderTakenVoorLid($uid);
		if (sizeof($taken) !== $aantal) {
			SimpleHTML::setMelding('Niet alle toekomstige corveetaken zijn verwijderd!', -1);
		}
		$changelog = 'Verwijderde corveetaken:';
		if ($aantal > 0) {
			foreach ($taken as $taak) {
				$changelog .= '[br]' . strftime('%a %e-%m-%Y', $taak->getBeginMoment()) . ' ' . $taak->getCorveeFunctie()->naam;
			}
			//corveeceasar mailen over vrijvallende corveetaken.
			$bericht = file_get_contents(SMARTY_TEMPLATE_DIR . 'MVC/mail/toekomstigcorveeverwijderd.mail');
			$values = array(
				'AANTAL' => $aantal,
				'NAAM'	 => Lid::naamLink($uid, 'full', 'plain'),
				'UID'	 => $uid,
				'OUD'	 => $oudestatus,
				'NIEUW'	 => $nieuwestatus,
				'CHANGE' => str_replace('[br]', "\n", $changelog),
				'ADMIN'	 => LoginModel::instance()->getLid()->getNaam()
			);
			$mail = new Mail('corvee@csrdelft.nl', 'Lid-af: toekomstig corvee verwijderd', $bericht);
			$mail->addBcc("pubcie@csrdelft.nl");
			$mail->setPlaceholders($values);
			$mail->send();
		}
		return $changelog;
	}

	/**
	 * Mail naar fisci over statuswijzigingen. Kunnen zij hun systemen weer mee updaten.
	 *
	 * @param string $oudestatus    voor wijziging
	 * @param string $nieuwestatus  na wijziging
	 * @return bool mailen is wel/niet verzonden
	 */
	private function notifyFisci($oudestatus, $nieuwestatus) {
		//saldi ophalen
		$saldi = '';
		foreach ($this->bewerktLid->getSaldi() as $saldo) {
			$saldi .= $saldo['naam'] . ': ' . $saldo['saldo'] . "\n";
		}

		$bericht = file_get_contents(SMARTY_TEMPLATE_DIR . 'MVC/mail/lidafmeldingfisci.mail');
		$uid = $this->bewerktLid->getUid();
		$values = array(
			'NAAM'	 => Lid::naamLink($uid, 'full', 'plain'),
			'UID'	 => $uid,
			'OUD'	 => $oudestatus,
			'NIEUW'	 => $nieuwestatus,
			'SALDI'	 => $saldi,
			'ADMIN'	 => LoginModel::instance()->getLid()->getNaam()
		);
		$to = 'fiscus@csrdelft.nl,maalcie-fiscus@csrdelft.nl,soccie@csrdelft.nl';

		$mail = new Mail($to, 'Melding lid-af worden', $bericht);
		$mail->addBcc("pubcie@csrdelft.nl");
		$mail->setPlaceholders($values);

		return $mail->send();
	}

	/**
	 * Mail naar bibliothecaris en leden over geleende boeken
	 *
	 * @param string $oudestatus    voor wijziging
	 * @param string $nieuwestatus  na wijziging
	 * @return bool mailen is wel/niet verzonden
	 */
	private function notifyBibliothecaris($oudestatus, $nieuwestatus) {
		require_once 'bibliotheek/catalogus.class.php';
		$boeken = Catalogus::getBoekenByUid($this->bewerktLid->getUid(), 'geleend');
		if (!is_array($boeken)) {
			$boeken = array();
		}
		//lijst van boeken genereren
		$bknleden = $bkncsr = array(
			'kopje'	 => '',
			'lijst'	 => '',
			'aantal' => 0
		);
		foreach ($boeken as $boek) {
			if ($boek['eigenaar_uid'] == 'x222') {
				$bkncsr['aantal'] ++;
				$bkncsr['lijst'] .= "{$boek['titel']} door {$boek['auteur']}\n";
				$bkncsr['lijst'] .= " - http://csrdelft.nl/communicatie/bibliotheek/boek/{$boek['id']}\n";
			} else {
				$bknleden['aantal'] ++;
				$bknleden['lijst'] .= "{$boek['titel']} door {$boek['auteur']}\n";
				$bknleden['lijst'] .= " - http://csrdelft.nl/communicatie/bibliotheek/boek/{$boek['id']}\n";
				$naam = Lid::naamLink($boek['eigenaar_uid'], 'full', 'plain');
				$bknleden['lijst'] .= " - boek is geleend van: $naam\n";
			}
		}
		//kopjes
		$mv = ($this->bewerktLid->getGeslacht() == 'v') ? 'haar' : 'hem';
		$enkelvoud = "Het volgende boek is nog door {$mv} geleend";
		$meervoud = "De volgende boeken zijn nog door {$mv} geleend";
		if ($bkncsr['aantal'])
			$bkncsr['kopje'] = ($bkncsr['aantal'] > 1 ? $meervoud : $enkelvoud) . " van de C.S.R.-bibliotheek:";
		if ($bknleden['aantal'])
			$bknleden['kopje'] = ($bknleden['aantal'] > 1 ? $meervoud : $enkelvoud) . " van leden:";

		// Alleen mailen als er C.S.R.boeken zijn
		if ($bkncsr['aantal'] == 0)
			return false;

		$to = 'bibliothecaris@csrdelft.nl,' . $this->bewerktLid->getEmail();
		$bericht = file_get_contents(SMARTY_TEMPLATE_DIR . 'MVC/mail/lidafgeleendebiebboeken.mail');
		$uid = $this->bewerktLid->getUid();
		$values = array(
			'NAAM'		 => Lid::naamLink($uid, 'full', 'plain'),
			'UID'		 => $uid,
			'OUD'		 => substr($oudestatus, 2),
			'NIEUW'		 => ($nieuwestatus == 'S_NOBODY' ? 'GEEN LID' : substr($nieuwestatus, 2)),
			'CSRLIJST'	 => $bkncsr['kopje'] . "\n" . $bkncsr['lijst'],
			'LEDENLIJST' => ($bkncsr['aantal'] > 0 ? "Verder ter informatie: " . $bknleden['kopje'] . "\n" . $bknleden['lijst'] : ''),
			'ADMIN'		 => LoginModel::instance()->getLid()->getNaam()
		);
		$mail = new Mail($to, 'Geleende boeken - Melding lid-af worden', $bericht);
		$mail->addBcc("pubcie@csrdelft.nl");
		$mail->setPlaceholders($values);

		return $mail->send();
	}

	/**
	 * Geeft array met per veld afhankelijk van status een boolean voor wel/niet bewaren en een resetwaarde.
	 * 
	 * @param $nieuwestatus string lidstatus
	 * @return array met per veld array met de entries: 
	 * 		'save': boolean voor wel/niet opslaan van gePOSTe waarde 
	 * 		'reset': mixed waarde in te vullen bij reset (null is nooit resetten)
	 *  Array(
	  ...
	  [postfix] => Array(		[save] =>	 1
	  [reset] => )
	  [lidafdatum] => Array(	[save] =>
	  [reset] => 0000-00-00 )
	  ...
	  )
	 */
	private function getFieldsToSave($nieuwestatus) {
		//per status: wel/niet bewaren van gePOSTe veldwaarde
		//Veldnamen:				status,	perm,	lidaf,	postfx,	ontvCntl,adr,	echtg,	strfd,	kring
		$bool['S_LID'] = array(true, true, false, true, false, false, false, false, true);
		$bool['S_NOVIET'] = $bool['S_LID'];
		$bool['S_GASTLID'] = $bool['S_LID'];
		$bool['S_OUDLID'] = array(true, true, true, false, true, true, true, false, true);
		$bool['S_ERELID'] = $bool['S_OUDLID'];
		$bool['S_KRINGEL'] = array(true, true, false, false, false, false, false, false, true);
		$bool['S_OVERLEDEN'] = array(true, true, true, false, false, false, false, true, false);
		$bool['S_NOBODY'] = array(true, true, true, false, false, false, false, false, true);
		$bool['S_EXLID'] = $bool['S_NOBODY'];
		$bool['S_CIE'] = array(true, true, false, false, false, false, false, false, false);

		$bools = $bool[$nieuwestatus];
		//'save' wordt gevuld met bovenstaande waardes
		//'reset' is waarde die ingevuld worden bij een reset (null = nooit resetten)
		$return['status'] = array('save' => $bools[0], 'reset' => null);
		$return['permissies'] = array('save' => $bools[1], 'reset' => null);
		$return['lidafdatum'] = array('save' => $bools[2], 'reset' => '0000-00-00');
		$return['postfix'] = array('save' => $bools[3], 'reset' => '');
		$return['ontvangtcontactueel'] = array('save' => $bools[4], 'reset' => null);
		$return['adresseringechtpaar'] = array('save' => $bools[5], 'reset' => null);
		$return['echtgenoot'] = array('save' => $bools[6], 'reset' => null);
		$return['sterfdatum'] = array('save' => $bools[7], 'reset' => null);
		$return['kring'] = array('save' => $bools[8], 'reset' => 0);

		return $return;
	}

}

class ProfielVoorkeur extends Profiel {

	public function __construct($lid, $actie) {
		parent::__construct($lid, $actie);
		$this->assignFields();
	}

	/**
	 * Defineert de velden van formulier voor het wijzigen van voorkeur
	 */
	public function assignFields() {
		LidCache::updateLid($this->lid->getUid());
		//permissies
		$opties = array(1 => 'nee', 2 => 'misschien', 3 => 'ja');
		require_once 'voorkeur/lidvoorkeur.class.php';
		$lidvoorkeur = new Lidvoorkeur($this->lid->getUid());
		$commissies = $lidvoorkeur->getCommissies();
		$voorkeuren = $lidvoorkeur->getVoorkeur();
		//status-select is eerste veld omdat die bij opslaan als eerste uitgelezen moet worden.
		foreach ($commissies as $id => $comm) {
			$form[] = new SelectField('comm' . $id, $this->getVoorkeur($voorkeuren, $id), $comm, $opties);
		}

		$form[] = new TextareaField('lidOpmerking', $lidvoorkeur->getLidOpmerking(), 'Vul hier je eventuele voorkeur voor functie in, of andere opmerkingen');
		$form[] = new FormButtons('/communicatie/profiel/' . $this->getUid());

		$this->form = new Formulier(null, 'profielForm', '/communicatie/profiel/' . $this->getUid() . '/voorkeuren');
		$this->form->addFields($form);
	}

	/**
	 * Slaat waardes uit de velden op.
	 *
	 * @return bool wel/niet slagen van opslaan van gegevens
	 */
	public function save() {
		//relevante gegevens uit velden verwerken
		$lidvoorkeur = new Lidvoorkeur($this->lid->getUid());
		if ($this->form->validate()) {
			foreach ($this->form->getValues() as $fieldname => $value) {
				//aan de hand van status bepalen welke POSTed velden worden opgeslagen van het formulier
				if ($fieldname == 'lidOpmerking') {
					$lidvoorkeur->setLidOpmerking($value);
				} else {
					$lidvoorkeur->setCommissieVoorkeur(substr($fieldname, 4), $value);
				}
			}
			return true;
		} else {
			return false;
		}
	}

	public function magBewerken() {
		//lid-moderator
		if (LoginModel::mag('P_LEDEN_MOD')) {
			return true;
		}
		//oudlid-moderator
		if (LoginModel::mag('P_LEDEN_MOD') AND in_array($this->lid->getStatus(), array('S_OUDLID', 'S_ERELID'))) {
			return true;
		}
		//of het gaat om ons eigen profiel.
		if (LoginModel::getUid() === $this->lid->getUid()) {
			return true;
		}
		return false;
	}

	private function getVoorkeur($voorkeur, $id) {
		if (array_key_exists($id, $voorkeur)) {
			return $voorkeur[$id];
		}
		return 0;
	}

}
