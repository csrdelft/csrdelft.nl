<?php

require_once 'lid/lidcache.class.php';
require_once 'MVC/view/Validator.interface.php';

/**
 * LoginModel.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * 
 * Role-based access control
 * 
 * Mapping van huidige ingeloggede lid (subject) met rollen en permissies.
 * En verder: inloggen, uitloggen, su'en etc.
 * 
 * @see http://en.wikipedia.org/wiki/Role-based_access_control
 */
class LoginModel extends PersistenceModel implements Validator {

	const orm = 'LoginSession';

	protected static $instance;
	/**
	 * Partially ordered Role Hierarchy:
	 * 
	 * TODO: A subject can have multiple roles.
	 * A role can have multiple subjects.
	 * A role can have many permissions.
	 * A permission can be assigned to many roles.
	 * An operation can be assigned many permissions.
	 * A permission can be assigned to many operations.
	 */
	private $roles = array();
	/**
	 * Permissies die we gebruiken om te vergelijken met de permissies van
	 * een gebruiker.
	 */
	private $permissions = array();
	/**
	 * Subject bevat het Lid-object van het lid dat op dit moment is ingelogd.
	 */
	private $subject;
	/**
	 * Mocht er gesued zijn, dan bevat suedFrom het oorspronkelijk ingelogde Lid,
	 * dus het lid dat de su heeft geïnitieerd.
	 */
	private $suedFrom = null;
	/**
	 * Komt de authenticatie van de huidige gebruiker uit een token in de url
	 * dan staat dit aan. aan LoginModel::mag() moet expliciet worden
	 * meegegeven dat we dit goed vinden, zodat deze validatie precies daar werkt
	 * waar we het willen, en niet op andere plekken.
	 */
	private $authenticatedByToken = false;
	/**
	 * Authetication error
	 */
	private $error;

	protected function __construct() {
		$this->loadPermissions();

		/**
		 * Werkomheen
		 * @source www.nabble.com/problem-with-sessions-in-1.4.8-t2550641.html
		 */
		if (session_id() == 'deleted') {
			session_regenerate_id();
		}

		// Staat er een gebruiker in de sessie?
		if (!$this->validate()) {
			// zo nee, dan nobody user er in gooien...
			// in dit geval is het de eerste keer dat we een pagina opvragen
			// of er is net uitgelogd waardoor de gegevens zijn leeggegooid
			$this->login('x999', 'x999', false);
		}

		// Als we x999 zijn checken we of er misschien een validatietoken in de $_GET staat
		// om zonder sessie bepaalde rechten te krijgen.
		if ($this->subject->getUid() === 'x999') {
			$token = filter_input(INPUT_GET, 'private_token', FILTER_SANITIZE_STRING);
			if (preg_match('/^[a-z0-9]{25}$/', $token)) {
				$uid = Database::instance()->sqlSelect(array('uid'), 'lid', 'rssToken = ?', array($token), null, null, 1)->fetchColumn();
				$lid = LidCache::getLid($uid);
				if ($lid instanceof Lid) {
					// Subject Assignment:
					$this->subject = $lid;
					$this->authenticatedByToken = true;
				}
			}
		}
		$this->logBezoek();
	}

	/**
	 * Is de huidige gebruiker al actief in een sessie?
	 */
	public function validate() {
		// Er is geen _uid gezet in _SESSION dus er is nog niemand ingelogged.
		if (!isset($_SESSION['_uid'])) {
			return false;
		}
		// Sessie is gekoppeld aan ip, het ip checken:
		if (isset($_SESSION['_ip']) AND $_SESSION['_ip'] !== $_SERVER['REMOTE_ADDR']) {
			return false;
		}
		$lid = LidCache::getLid($_SESSION['_uid']);
		if ($lid instanceof Lid) {
			// Subject Assignment:
			$this->subject = $lid;

			if (isset($_SESSION['_suedFrom'])) {
				$this->suedFrom = LidCache::getLid($_SESSION['_suedFrom']);
			}
			return true;
		}
		return false;
	}

	/**
	 * Na opvragen resetten.
	 * 
	 * @return mixed null or string
	 */
	public function getError() {
		if (isset($_SESSION['auth_error'])) {
			$this->error = $_SESSION['auth_error'];
			unset($_SESSION['auth_error']);
		}
		return $this->error;
	}

	public function isPauper() {
		return isset($_SESSION['pauper']) AND $_SESSION['pauper'] === true;
	}

	public function setPauper($value) {
		if ($value) {
			$_SESSION['pauper'] = true;
		} else {
			unset($_SESSION['pauper']);
		}
	}

	/**
	 * @deprecated Remove after MVC refactor is complete
	 */
	private function logBezoek() {
		$db = MijnSqli::instance();
		$uid = $this->subject->getUid();
		$datumtijd = getDateTime();
		$locatie = '';
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = $db->escape($_SERVER['REMOTE_ADDR']);
		} else {
			$ip = '0.0.0.0';
			$locatie = '';
		}
		if (isset($_SERVER['REQUEST_URI'])) {
			$url = $db->escape($_SERVER['REQUEST_URI']);
		} else {
			$url = '';
		}
		if (isset($_SERVER['HTTP_REFERER'])) {
			$referer = $db->escape($_SERVER['HTTP_REFERER']);
		} else {
			$referer = '';
		}
		$agent = '';
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$agent = $db->escape($_SERVER['HTTP_USER_AGENT']);
		}
		$sLogQuery = "
			INSERT INTO
					log
			(
					uid, ip, locatie, moment, url, referer, useragent
			)VALUES(
					'" . $uid . "', '" . $ip . "', '" . $locatie . "', '" . $datumtijd . "', '" . $url . "', '" . $referer . "', '" . $agent . "'
			);";
		if (!preg_match('/stats.php/', $url) AND $ip != '0.0.0.0') {
			$db->query($sLogQuery);
		}
	}

	/**
	 * Dispatch the login proces to a separate function based on MODE.
	 * 
	 * @param type $user
	 * @param type $pass
	 * @param type $checkip
	 * @return boolean
	 */
	public function login($user, $pass = '', $checkip = true) {
		$user = filter_var($user, FILTER_SANITIZE_STRING);
		$pass = filter_var($pass, FILTER_SANITIZE_STRING);
		switch (constant('MODE')) {
			case 'CLI':
				return $this->loginCli();
			case 'WEB':
			default:
				return $this->loginWeb($user, $pass, (boolean) $checkip);
		}
	}

	/**
	 * Grant cli access for cron.
	 * 
	 * @param type $user
	 * @return boolean
	 */
	private function loginCli() {
		if (defined('ETC_PATH')) {
			$cred = parse_ini_file(ETC_PATH . 'cron.ini');
		} else {
			$cred = array(
				'user'	 => 'cron',
				'pass'	 => 'pw'
			);
		}
		return $this->loginWeb($cred['user'], $cred['pass'], false);
	}

	/**
	 * Als een gebruiker wordt ingelogd met ipcheck==true, dan wordt het IP-adres
	 * van de gebruiker opgeslagen in de sessie, en het sessie-cookie zal alleen
	 * vanaf dat adres toegang geven tot de website.
	 * 
	 * @param string $user
	 * @param string $pass
	 * @param boolean $checkip
	 * @return boolean
	 */
	private function loginWeb($user, $pass, $checkip = true) {
		$lid = false;
		// eerst met uid proberen, komt daar een zinnige gebruiker uit, die gebruiken.
		if (Lid::isValidUid($user)) {
			$lid = LidCache::getLid($user);
		}
		// als er geen lid-object terugkomt, proberen we het met de nickname:
		if (!($lid instanceof Lid)) {
			$lid = Lid::loadByNickname($user);
		}
		// als er geen lid-object terugkomt, proberen we het met de duckname:
		if (!($lid instanceof Lid)) {
			$lid = Lid::loadByDuckname($user);
		}
		// als er geen lid-object terugkomt, haken we af
		// als we hebben nu een gebruiker gevonden en gaan eerst het wachtwoord controleren
		if (!($lid instanceof Lid) OR ! $lid->checkpw($pass)) {
			$_SESSION['auth_error'] = 'Het spijt ons heel erg, maar met de gegeven
				inloggegevens is het niet mogelijk in te loggen. Zou het
				eventueel mogelijk zijn dat u, geheel per ongeluk, een fout heeft
				gemaakt met invoeren? In dat geval bieden wij u onze nederige
				excuses aan en vragen wij u het nog eens te proberen.';
			return false;
		}

		// als dat klopt laden we het profiel in en richten de sessie in
		// Subject Assignment:
		$this->subject = $lid;
		$_SESSION['_uid'] = $lid->getUid();

		// sessie koppelen aan ip?
		if ($checkip == true) {
			$_SESSION['_ip'] = $_SERVER['REMOTE_ADDR'];
		} elseif (isset($_SESSION['_ip'])) {
			unset($_SESSION['_ip']);
		}
		return true;
	}

	public function logout() {
		session_unset();
		$this->login('x999', 'x999', true);
	}

	public function switchUser($uid) {
		if (!Lid::isValidUid($uid)) {
			throw new Exception('Invalid UID');
		}
		if ($this->isSued()) {
			throw new Exception('Geneste su niet mogelijk!');
		}
		if ($uid == 'x999') {
			throw new Exception('Ja, log dan maar lekker uit!');
		}
		if ($this->getUid() === $uid) {
			throw new Exception('Dit ben je zelf!');
		}
		$suNaar = LidCache::getLid($uid);
		if (in_array($suNaar->getStatus(), array('S_NOBODY', 'S_EXLID'))) {
			throw new Exception('Kan niet su-en naar S_NOBODY of S_EXLID');
		}
		$_SESSION['_suedFrom'] = $this->subject->getUid();
		$_SESSION['_uid'] = $uid;
		// Subject Assignment:
		$this->subject = $suNaar;
	}

	public function endSwitchUser() {
		$_SESSION['_uid'] = $_SESSION['_suedFrom'];
		// Subject Assignment:
		$this->subject = $this->suedFrom;
		unset($_SESSION['_suedFrom']);
		$this->suedFrom = null;
	}

	public function isSued() {
		return $this->suedFrom !== null;
	}

	public function getSuedFrom() {
		return $this->suedFrom;
	}

	public function maySuTo(Lid $lid) {
		return !$this->isSued() AND $lid->getUid() !== $this->getUid() AND $lid->getUid() !== 'x999' AND ! in_array($lid->getStatus(), array('S_NOBODY', 'S_EXLID'));
	}

	public static function getUid() {
		if (!isset(static::$instance)) {
			throw new Exception('LoginModel not initialized!');
		}
		return LoginModel::instance()->getLid()->getUid();
	}

	public function getLid() {
		return $this->subject;
	}

	public function getValidPerms() {
		return array_keys($this->permissions);
	}

	public function isValidPerm($perm) {
		if (isset($this->permissions[$perm])) {
			return true;
		}
		return false;
	}

	public function isValidRole($role) {
		if (isset($this->roles[$role])) {
			return true;
		}
		return false;
	}

	/**
	 * Hier staan de permissies die voor enkele onderdelen van de website nodig zijn.
	 *
	 * Ze worden zowel op de 'echte' website als in het beheergedeelte gebruikt.
	 * READ = Rechten om het onderdeel in te zien
	 * POST = Rechten om iets toe te voegen
	 * MOD  = Moderate rechten, dus verwijderen enzo
	 * 
	 * Let op: de rechten zijn cumulatief (bijv: 7=4+2+1, 3=2+1)
	 * als je hiervan afwijkt, kun je (bewust) niveau's uitsluiten (bijv 5=4+1, sluit 2 uit)
	 * de levels worden omgezet in een karakter met die ASCII waarde (dit zijn vaak niet-leesbare symbolen, bijv #8=backspace)
	 * elke karakter van een string representeert een onderdeel
	 *
	 */
	private function loadPermissions() {
		$this->permissions = array(
			'P_PUBLIC'			 => $this->createPermStr(0, 0), // Iedereen op het Internet
			'P_LOGGED_IN'		 => $this->createPermStr(1, 0), // Leden-menu, eigen profiel raadplegen
			'P_PROFIEL_EDIT'	 => $this->createPermStr(1 + 2, 0), // Eigen gegevens aanpassen
			'P_ALLEEN_OUDLID'	 => $this->createPermStr(4, 0), // Specifiek voor oudleden [[let op: niet cumulatief]]
			'P_VERJAARDAGEN'	 => $this->createPermStr(1, 1), // Verjaardagen van leden zien
			'P_LEDEN_READ'		 => $this->createPermStr(1 + 2, 1), // Gegevens van leden raadplegen
			'P_OUDLEDEN_READ'	 => $this->createPermStr(1 + 2 + 4, 1), // Gegevens van oudleden raadplegen
			'P_LEDEN_MOD'		 => $this->createPermStr(1 + 2 + 4 + 6, 1), // (Oud)ledengegevens aanpassen
			'P_FORUM_READ'		 => $this->createPermStr(1, 2), // Forum lezen
			'P_FORUM_POST'		 => $this->createPermStr(1 + 2, 2), // Berichten plaatsen op het forum en eigen berichten wijzigen
			'P_FORUM_MOD'		 => $this->createPermStr(1 + 2 + 4, 2), // Forum-moderator mag berichten van anderen wijzigen of verwijderen
			'P_FORUM_BELANGRIJK' => $this->createPermStr(8, 2), // Forum belangrijk (de)markeren  [[let op: niet cumulatief]]
			'P_FORUM_ADMIN'		 => $this->createPermStr(16, 2), // Forum-admin mag deel-fora aanmaken en rechten wijzigen  [[let op: niet cumulatief]]
			'P_AGENDA_READ'		 => $this->createPermStr(1, 3), // Agenda bekijken
			'P_AGENDA_ADD'		 => $this->createPermStr(1 + 2, 3), // Items toevoegen aan de agenda
			'P_AGENDA_MOD'		 => $this->createPermStr(1 + 2 + 4, 3), // Items beheren in de agenda
			'P_DOCS_READ'		 => $this->createPermStr(1, 4), // Documenten-rubriek lezen
			'P_DOCS_POST'		 => $this->createPermStr(1 + 2, 4), // Documenten verwijderen of erbij plaatsen
			'P_DOCS_MOD'		 => $this->createPermStr(1 + 2 + 4, 4), // Documenten aanpassen
			'P_ALBUM_READ'		 => $this->createPermStr(1, 5), // Foto-album bekijken
			'P_ALBUM_DOWN'		 => $this->createPermStr(1 + 2, 5), // Foto-album downloaden
			'P_ALBUM_ADD'		 => $this->createPermStr(1 + 2 + 4, 5), // Fotos uploaden en albums toevoegen
			'P_ALBUM_MOD'		 => $this->createPermStr(1 + 2 + 4 + 8, 5), // Foto-albums aanpassen
			'P_ALBUM_DEL'		 => $this->createPermStr(1 + 2 + 4 + 8 + 16, 5), // Fotos uit fotoalbum verwijderen
			'P_BIEB_READ'		 => $this->createPermStr(1, 6), // Bibliotheek lezen
			'P_BIEB_EDIT'		 => $this->createPermStr(1 + 2, 6), // Bibliotheek wijzigen
			'P_BIEB_MOD'		 => $this->createPermStr(1 + 2 + 4, 6), // Bibliotheek zowel wijzigen als lezen
			'P_NEWS_POST'		 => $this->createPermStr(1, 7), // Nieuws plaatsen en wijzigen van jezelf
			'P_NEWS_MOD'		 => $this->createPermStr(1 + 2, 7), // Nieuws-moderator mag berichten van anderen wijzigen of verwijderen
			'P_NEWS_PUBLISH'	 => $this->createPermStr(1 + 2 + 4, 7), // Nieuws publiceren en rechten bepalen
			'P_MAAL_IK'			 => $this->createPermStr(1, 8), // kan zich aan en afmelden voor maaltijd en eigen abo wijzigen
			'P_MAAL_MOD'		 => $this->createPermStr(1 + 2, 8), // mag maaltijden beheren (MaalCie P)
			'P_MAAL_SALDI'		 => $this->createPermStr(1 + 2 + 4, 8), // mag het MaalCie saldo aanpassen van iedereen (MaalCie fiscus)
			'P_CORVEE_IK'		 => $this->createPermStr(1, 9), // kan voorkeuren aangeven voor corveetaken
			'P_CORVEE_MOD'		 => $this->createPermStr(1 + 2, 9), // mag corveetaken beheren (CorveeCaesar)
			'P_CORVEE_SCHED'	 => $this->createPermStr(1 + 2 + 4, 9), // mag de automatische corvee-indeler beheren
			'P_MAIL_POST'		 => $this->createPermStr(1, 10), // mag berichtjes in de courant rossen
			'P_MAIL_COMPOSE'	 => $this->createPermStr(1 + 2, 10), // mag alle berichtjes in de courant bewerken, en volgorde wijzigen
			'P_MAIL_SEND'		 => $this->createPermStr(1 + 2 + 4, 10), // mag de courant verzenden
			'P_ADMIN'			 => $this->createPermStr(1, 11) // Super-admin
		);
		/**
		 * Deze waarden worden samengesteld uit bovenstaande permissies en
		 * worden in de gebruikersprofielen gebruikt als aanduiding voor
		 * welke permissie-groep (Role) de gebruiker in zit (max. 1 momenteel).
		 */
		$p = $this->permissions;

		// Permission Assignment:
		$this->roles = array(
			'R_NOBODY'	 => $p['P_PUBLIC'] | $p['P_FORUM_READ'] | $p['P_AGENDA_READ'] | $p['P_ALBUM_READ'],
			'R_LID'		 => $p['P_PROFIEL_EDIT'] | $p['P_OUDLEDEN_READ'] | $p['P_FORUM_POST'] | $p['P_AGENDA_READ'] | $p['P_DOCS_READ'] | $p['P_BIEB_READ'] | $p['P_MAAL_IK'] | $p['P_CORVEE_IK'] | $p['P_MAIL_POST'] | $p['P_NEWS_POST'] | $p['P_ALBUM_MOD']
		);

		// use | $p[] for hierarchical RBAC (inheritance between roles)
		// use & ~$p[] for constrained RBAC (separation of duties)

		$this->roles['R_ETER'] = $this->roles['R_NOBODY'] | $p['P_LOGGED_IN'] | $p['P_PROFIEL_EDIT'] | $p['P_MAAL_IK'];
		$this->roles['R_OUDLID'] = $this->roles['R_LID'] | $p['P_ALLEEN_OUDLID'];
		$this->roles['R_BASF'] = $this->roles['R_LID'] | $p['P_DOCS_MOD'] | $p['P_ALBUM_DEL'];
		$this->roles['R_MAALCIE'] = $this->roles['R_LID'] | $p['P_MAAL_MOD'] | $p['P_CORVEE_MOD'] | $p['P_MAAL_SALDI'];
		$this->roles['R_MODERATOR'] = $this->roles['R_LID'] | $p['P_LEDEN_MOD'] | $p['P_FORUM_MOD'] | $p['P_DOCS_MOD'] | $p['P_AGENDA_MOD'] | $p['P_NEWS_MOD'] | $p['P_BIEB_MOD'] | $p['P_MAAL_IK'] | $p['P_CORVEE_IK'] | $p['P_MAIL_COMPOSE'] | $p['P_ALBUM_DEL'];
		$this->roles['R_BESTUUR'] = $this->roles['R_MODERATOR'] | $p['P_MAAL_MOD'] | $p['P_CORVEE_MOD'] | $p['P_MAIL_COMPOSE'] | $p['P_FORUM_BELANGRIJK'];
		$this->roles['R_PUBCIE'] = $this->roles['R_MODERATOR'] | $p['P_ADMIN'] | $p['P_MAIL_SEND'] | $p['P_CORVEE_SCHED'] | $p['P_MAAL_SALDI'] | $p['P_FORUM_ADMIN'];
	}

	/**
	 * Create permission string with character which has ascii value of request level.
	 *
	 * @param int $level           permissiewaarde
	 * @param int $onderdeelnummer starts at zero
	 * @return string permission string
	 */
	private function createPermStr($level, $onderdeelnummer) {
		$nulperm = str_repeat(chr(0), 15);
		return substr_replace($nulperm, chr($level), $onderdeelnummer, 1);
	}

	/**
	 * @param string $descr permissie(s).
	 * @param boolean $token_authorizable als false dan werkt mag alsof gebruiker
	 * 						x999 is, als true dan wordt op de permissies van
	 * 						de met de token geäuthenticeerde gebruiker getest
	 * @param boolean $mandatory_only sta alleen permissies toe op basis van rol
	 * 
	 * Met deze functies kan op één of meerdere permissies worden getest,
	 * onderling gescheiden door komma's. Als een lid één van de
	 * permissies 'heeft', geeft de functie true terug. Het is dus een
	 * logische OF tussen de verschillende te testen permissies. Een
	 * permissie kan met een uitroepteken geïnverteerd worden.
	 * 
	 * Voorbeeldjes:
	 *  groep:novcie				geeft true leden van de h.t. NovCie.
	 *  groep:pubcie,groep:bestuur	geeft true voor leden van h.t. bestuur en h.t. novcie
	 *  groep:SocCie>Fiscus			geeft true voor h.t. Soccielid met functie fiscus
	 *  geslacht:m					geeft true voor alle mannelijke leden
	 *  verticale:d					geeft true voor alle leden van verticale d.
	 *  !lichting:2009				geeft true voor iedereen behalve lichting 2009.
	 * 
	 * Gecompliceerde voorbeeld:
	 * 		groep:novcie+groep:wcie|1137,groep:bestuur
	 * 
	 * Equivalent met haakjes:
	 * 		(groep:novcie EN (groep:wcie OF 1137)) OF groep:bestuur
	 * 
	 * Geeft toegang aan:
	 * 		de mensen die én in de NovCie zitten én in de WCie zitten
	 * 		of mensen die in de NovCie zitten en lidnummer 1137 hebben
	 * 		of mensen die in het bestuur zitten
	 * 
	 */
	public static function mag($descr, $token_authorizable = false, $mandatory_only = false) {
		// OR
		if (strpos($descr, ',') !== false) {
			// Het gevraagde mag een enkele permissie zijn, of meerdere, door komma's
			// gescheiden, waarvan de gebruiker er dan een hoeft te hebben. Er kunnen
			// dan ook uid's tussen zitten, als een daarvan gelijk is aan dat van de
			// gebruiker heeft hij ook rechten.
			$permissies = explode(',', $descr);
			$result = false;
			foreach ($permissies as $permissie) {
				$result |= self::mag($permissie, $token_authorizable, $mandatory_only);
			}
			return $result;
		}
		// AND
		if (strpos($descr, '+') !== false) {
			// Gecombineerde permissie:
			// gebruiker moet alle permissies bezitten
			$permissies = explode('+', $descr);
			$result = true;
			foreach ($permissies as $permissie) {
				$result &= self::mag($permissie, $token_authorizable, $mandatory_only);
			}
			return $result;
		}
		// OR (secondary)
		if (strpos($descr, '|') !== false) {
			// Mogelijkheid voor OR binnen een AND
			// Hierdoor zijn er geen haakjes nodig in de syntax voor niet al te ingewikkelde statements.
			// Statements waarbij haakjes wel nodig zijn moet je niet willen.
			$permissies = explode('|', $descr);
			$result = false;
			foreach ($permissies as $permissie) {
				$result |= self::mag($permissie, $token_authorizable, $mandatory_only);
			}
			return $result;
		}
		// Negatie van een permissie (gebruiker mag deze permissie niet bezitten)
		if (substr($descr, 0, 1) === '!' && !self::mag(substr($descr, 1), $token_authorizable, $mandatory_only)) {
			return true;
		}

		return LoginModel::instance()->hasPermission($descr, $token_authorizable, $mandatory_only);
	}

	private function hasPermission($descr, $token_authorizable, $mandatory_only) {

		// Als er een geldige permissie wordt gevraagd
		if ($this->isValidPerm($descr)) {
			return $this->mandatoryAccessControl($descr, $token_authorizable);
		} elseif (!$mandatory_only) {
			return $this->discretionaryAccessControl($descr, $token_authorizable);
		}

		return false;
	}

	private function mandatoryAccessControl($permission, $token_authorizable) {

		// zoek de rechten van de gebruiker op
		$role = $this->subject->getRole();

		// alleen als $token_authorizable true is testen we met de permissies van het
		// geauthenticeerde lid, anders met R_NOBODY
		if ($this->authenticatedByToken AND ! $token_authorizable) {
			$role = 'R_NOBODY';
		}

		// ga alleen verder als er een geldige permissie wordt teruggegeven
		if (!$this->isValidRole($role)) {
			return false;
		}

		// zoek de codes op
		$gevraagd = $this->permissions[$permission];
		$lidheeft = $this->roles[$role];

		/**
		 * permissies zijn een string, waarin elk kararakter de
		 * waarde heeft van een permissielevel voor een bepaald onderdeel.
		 * 
		 * de mogelijke *verschillende* permissies voor een onderdeel zijn machten van twee:
		 * 1, 2, 4, 8, etc
		 * elk van deze waardes kan onderscheiden worden in een permissie, ook als je ze met elkaar combineert
		 * bijv.  3=1+2, 7=1+2+4, 5=1+4, 6=2+4, 12=4+8, etc
		 * 
		 * $gevraagd is de gevraagde permissie als string,
		 * de permissies van de gebruiker $lidheeft kunnen we bij $this->lid opvragen
		 * als we die 2 met elkaar AND-en, dan moet het resultaat hetzelfde
		 * zijn aan de gevraagde permissie. In dat geval bestaat de permissie
		 * van het lid dus minimaal uit de gevraagde permissie
		 * 
		 * Bij het AND-en, wordt elke karakter bitwise vergeleken, dat betekent:
		 * - elke karakter van de string omzetten in de ASCII-waarde
		 *   (bijv. ?=63, A=65, a=97, etc zie ook http:* www.ascii.cl/)
		 * - deze ASCII-waarde omzetten in een binaire getal
		 *   (bijv. 2=00010, 4=00100, 5=00101, 14=01110, etc)
		 * - de bits van het binaire getal een-voor-een vergelijken met de bits van het binaire getal uit de
		 *   andere string. Als ze overeenkomen worden ze bewaard.
		 *   (bijv. 3&5=1 => 00011&00101=00001)
		 * 
		 * voorbeeld (met de getallen 0 tot 7 als ASCII-waardes ipv de symbolen, voor de leesbaarheid)
		 * gevraagd:  P_FORUM_MOD : 0000000700
		 * lid heeft: R_LID       : 0005544500
		 * AND resultaat          : 0000000500 -> is niet wat gevraagd is -> weiger
		 * 
		 * gevraagd:  P_DOCS_READ : 0000004000
		 * lid heeft: R_LID       : 0005544500
		 * AND resultaat          : 0000004000 -> ja!
		 * 
		 */
		$resultaat = $gevraagd & $lidheeft;

		if ($resultaat === $gevraagd) {
			return true;
		}

		return false;
	}

	private function discretionaryAccessControl($descr, $token_authorizable) {

		// als een uid ingevoerd wordt true teruggeven als het om de huidige gebruiker gaat.
		if ($descr === $this->getUid()) {
			return true;
		}
		// Behoort een lid tot een bepaalde verticale?
		elseif (substr($descr, 0, 9) === 'verticale') {
			$verticale = strtoupper(substr($descr, 10));
			if (is_numeric($verticale)) {
				if ($verticale == $this->subject->getVerticaleID()) {
					return true;
				}
			} elseif ($verticale == $this->subject->getVerticaleLetter()) {
				return true;
			} elseif ($verticale == strtoupper($this->subject->getVerticale())) {
				return true;
			}
		}
		// Behoort een lid tot een bepaalde (h.t.) groep?
		// als een string als bijvoorbeeld 'pubcie' wordt meegegeven zoekt de ketzer
		// de h.t. groep met die korte naam erbij, als het getal is uiteraard de groep
		// met dat id.
		// met de toevoeging '>Fiscus' kan ook specifieke functie geëist worden binnen een groep
		elseif (substr($descr, 0, 5) === 'groep') {
			require_once 'groepen/groep.class.php';
			// splitst opgegeven term in groepsnaam en functie
			$parts = explode(">", substr($descr, 6), 2);
			try {
				$groep = new OldGroep($parts[0]);
				if ($groep->isLid()) {
					// wordt er een functie gevraagd?
					if (isset($parts[1])) {
						$functie = $groep->getFunctie();
						if (strtolower($functie[0]) == strtolower($parts[1])) {
							return true;
						}
					} else {
						return true;
					}
				}
			} catch (Exception $e) {
				// de groep bestaat niet, we gaan verder.
			}
		}
		// Is een lid man, vrouw en/of geslacht?
		elseif (substr($descr, 0, 8) === 'geslacht') {
			$geslacht = strtolower(substr($descr, 9));
			if ($geslacht === $this->subject->getGeslacht()) {
				return true;
			}
			// we zijn toch zeker niet geslacht??
			elseif ($geslacht === 'nee' AND $this->hasPermission('P_LOGGED_IN', $token_authorizable, true)) {
				return true;
			}
		}
		// Behoort een lid tot een bepaalde lichting?
		elseif (substr($descr, 0, 7) === 'lidjaar') {
			$lidjaar = substr($descr, 8);
			if ($lidjaar == $this->subject->getProperty('lidjaar')) {
				return true;
			}
		} elseif (substr($descr, 0, 8) === 'lichting') {
			$lidjaar = substr($descr, 9);
			if ($lidjaar == $this->subject->getProperty('lidjaar')) {
				return true;
			}
		} elseif (substr($descr, 0, 10) === 'Ouderjaars' OR substr($descr, 0, 10) === 'ouderjaars') {
			if (Lichting::getJongsteLichting() > $this->subject->getProperty('lidjaar') AND $this->hasPermission('P_LOGGED_IN', $token_authorizable, true)) {
				return true;
			}
		} elseif (substr($descr, 0, 11) === 'Eerstejaars' OR substr($descr, 0, 11) === 'eerstejaars') {
			if (Lichting::getJongsteLichting() == $this->subject->getProperty('lidjaar') AND $this->hasPermission('P_LOGGED_IN', $token_authorizable, true)) {
				return true;
			}
		}

		return false;
	}

}
