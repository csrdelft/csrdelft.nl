<?php

require_once 'MVC/model/Agendeerbaar.interface.php';
require_once 'lid.class.php';
require_once 'lichting.class.php';

/**
 * loginlid.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 *
 * Bewaart het huidige ingeloggede lid, inloggen, uitloggen, rechten.
 */
class LoginLid {

	private static $instance;

	# permissies die we gebruiken om te vergelijken met de permissies van
	# een gebruiker. zie functie _loadPermissions()
	protected $_permissions = array();
	protected $_perm_user = array();

	# $lid bevat het Lid-object van het lid dat op dit moment is ingelogd.
	private $lid;

	# mocht er gesued zijn, dan bevat suedFrom het oorspronkelijk ingelogde Lid,
	# dus het lid dat de su heeft geïnitieerd.
	private $suedFrom = null;

	/* Komt de authenticatie van de huidige gebruiker uit een token in de url
	 * dan staat dit aan. aan LoginLid::hasPermission() moet expliciet worden
	 * meegegeven dat we dit goed vinden, zodat deze validatie precies daar werkt
	 * waar we het willen, en niet op andere plekken.
	 */
	private $authenticatedByToken = false;

	/**
	 * De enige instantie van LoginLid
	 * @return LoginLid
	 */
	public static function instance() {
		//als er nog geen instantie gemaakt is, die nu maken
		if (!isset(self::$instance)) {
			self::$instance = new LoginLid();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->_loadPermissions();

		# http://www.nabble.com/problem-with-sessions-in-1.4.8-t2550641.html
		if (session_id() == 'deleted')
			session_regenerate_id();

		//Staat er een gebruiker in de sessie?
		if (!$this->userIsActive()) {
			# zo nee, dan nobody user er in gooien...
			# in dit geval is het de eerste keer dat we een pagina opvragen
			# of er is net uitgelogd waardoor de gegevens zijn leeggegooid
			$this->login('x999', 'x999', false);
		}
		/* Als we x999 zijn checken we of er misschien een validatietoken in de $_GET staat
		 * om zonder sessie bepaalde rechten te krijgen.
		 */
		if ($this->getUid() == 'x999') {
			if (isset($_GET['validate_token']) AND preg_match('/^[a-z0-9]{25}$/', $_GET['validate_token'])) {
				$db = MySql::instance();
				$query = "
					SELECT uid
					FROM lid
					WHERE rssToken='" . $db->escape($_GET['validate_token']) . "'
					LIMIT 1;";
				$lid = $db->getRow($query);

				$lid = LidCache::getLid($lid['uid']);
				if ($lid instanceof Lid) {
					$this->lid = $lid;
					$this->authenticatedByToken = true;
				}
			}
		}

		$this->logBezoek();
	}

	/*
	 * Is de huidige gebruiker al actief in een sessie?
	 */

	private function userIsActive() {
		//er is geen _uid gezet in _SESSION dus er is nog niemand ingelogged.
		if (!isset($_SESSION['_uid'])) {
			return false;
		}
		//Sessie is gekoppeld aan ip, het ip checken:
		if (isset($_SESSION['_ip']) AND $_SERVER['REMOTE_ADDR'] != $_SESSION['_ip']) {
			return false;
		}
		$lid = LidCache::getLid($_SESSION['_uid']);
		if ($lid instanceof Lid) {
			$this->lid = $lid;

			if (isset($_SESSION['_suedFrom'])) {
				$this->suedFrom = LidCache::getLid($_SESSION['_suedFrom']);
			}
			return true;
		} else {
			return false;
		}
	}

	public function getUid() {
		return $this->lid->getUid();
	}

	public function getLid() {
		return $this->lid;
	}

	public function isSelf($uid) {
		return $this->lid->getUid() == $uid;
	}

	/**
	 * Switch-user-functies, handig om de webstek snel even te bekijken alsof
	 * je iemand anders bent.
	 */
	public function su($uid) {
		if (!Lid::isValidUid($uid)) {
			throw new Exception('Geen geldig uid opgegeven!');
		}
		if ($this->isSued()) {
			throw new Exception('Geneste su niet mogelijk!');
		}
		if ($uid == 'x999') {
			throw new Exception('Ja, log dan maar lekker uit!');
		}
		if ($this->isSelf($uid)) {
			throw new Exception('Dit ben je zelf!');
		}
		$suNaar = LidCache::getLid($uid);
		if (in_array($suNaar->getStatus(), array('S_NOBODY', 'S_EXLID'))) {
			throw new Exception('Kan niet su-en naar nobodies!');
		}
		$_SESSION['_suedFrom'] = $this->lid->getUid();
		$_SESSION['_uid'] = $uid;
		$this->lid = $suNaar;
	}

	public function endSu() {
		$_SESSION['_uid'] = $_SESSION['_suedFrom'];
		$this->lid = $this->suedFrom;
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
		return !$this->isSelf($lid->getUid()) AND $lid->getUid() != 'x999' && !$this->isSued() && !in_array($lid->getStatus(), array('S_NOBODY', 'S_EXLID'));
	}

	# dispatch the login proces to a separate function based on MODE

	public function login($user, $pass = "", $checkip = true) {
		switch (constant('MODE')) {
			case 'CLI':
				return $this->_login_cli($user);
			case 'BOT':
				return $this->_login_bot($user);
			case 'WEB':
			default:
				return $this->_login_web($user, $pass, $checkip);
		}
	}

	# als een gebruiker wordt ingelogd met ipcheck==true, dan wordt het IP-adres
	# van de gebruiker opgeslagen in de sessie, en het sessie-cookie zal alleen
	# vanaf dat adres toegang geven tot de website

	private function _login_web($user, $pass, $checkip = true) {
		$lid = false;
		//eerst met uid proberen, komt daar een zinnige gebruiker uit, die gebruiken.
		if (Lid::isValidUid($user)) {
			$lid = LidCache::getLid($user);
		}
		//als er geen lid-object terugkomt, proberen we het met de nickname:
		if (!($lid instanceof Lid)) {
			$lid = Lid::loadByNickname($user);
			if (!($lid instanceof Lid)) {
				return false;
			}
		}

		# we hebben nu een gebruiker gevonden en gaan eerst het wachtwoord controleren
		if (!$lid->checkpw($pass)) {
			return false;
		}

		# als dat klopt laden we het profiel in en richten de sessie in
		$this->lid = $lid;
		$_SESSION['_uid'] = $lid->getUid();

		# sessie koppelen aan ip?
		if ($checkip == true) {
			$_SESSION['_ip'] = $_SERVER['REMOTE_ADDR'];
		} elseif (isset($_SESSION['_ip'])) {
			unset($_SESSION['_ip']);
		}
		return true;
	}

	# login without a password, only for BOT use
	# only uids are supported, no nicknames

	private function _login_bot($user) {
		$lid = false;
		//eerst met uid proberen, komt daar een zinnige gebruiker uit, die gebruiken.
		if (Lid::isValidUid($user)) {
			$lid = LidCache::getLid($user);
			if ($lid instanceof Lid) {
				$this->lid = $lid;
				return true;
			}
		}
		return false;
	}

	# TODO: implement this

	private function _login_cli($user) {
		return false;
	}

	public function logout() {
		session_unset();
		$this->login('x999', 'x999', true);
	}

	public static function instelling($module, $key = null) {
		return LidInstellingen::get($module, $key);
	}

	public function getInstelling($module, $key = null) {
		return LidInstellingen::get($module, $key);
	}

	/**
	 * static hasPermission:
	 *
	 * @descr				een string met permissie(s).
	 * @token_authorizable	als false dan werkt hasPermission alsof gebruiker
	 * 						x999 is, als true dan wordt op de permissies van
	 * 						de met de token geäuthenticeerde gebruiker getest
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
	 */
	public static function mag($descr, $token_authorizable = false) {
		return LoginLid::instance()->hasPermission($descr, $token_authorizable);
	}

	public function hasPermission($descr, $token_authorizable = false) {
		# zoek de rechten van de gebruiker op
		$liddescr = $this->lid->getPermissies();

		//alleen als $token_athorizable true is testen we met de permissies van het
		//geauthenticeerde lid, anders met P_NOBODY
		if ($this->authenticatedByToken AND ! $token_authorizable) {
			$liddescr = 'P_NOBODY';
		}

		# ga alleen verder als er een geldige permissie wordt teruggegeven
		if (!array_key_exists($liddescr, $this->_perm_user)) {
			return false;
		}
		# zoek de code op
		$lidheeft = $this->_perm_user[$liddescr];

		if (strpos($descr, ',') !== false) {
			# Het gevraagde mag een enkele permissie zijn, of meerdere, door komma's
			# gescheiden, waarvan de gebruiker er dan een hoeft te hebben. Er kunnen
			# dan ook uid's tussen zitten, als een daarvan gelijk is aan dat van de
			# gebruiker heeft hij ook rechten.
			$permissies = explode(',', $descr);
			$result = false;
			foreach ($permissies as $permissie) {
				$result |= $this->hasPermission($permissie, $token_authorizable);
			}
			return $result;
		}
		if (strpos($descr, '+') !== false) {
			# Gecombineerde permissie:
			# gebruiker moet alle permissies bezitten
			$permissies = explode('+', $descr);
			$result = true;
			foreach ($permissies as $permissie) {
				$result &= $this->hasPermission($permissie, $token_authorizable);
			}
			return $result;
		}
		$permissie = trim($descr);
		# Negatie van een permissie:
		# gebruiker mag deze permissie niet bezitten
		if (substr($permissie, 0, 1) == '!' && !$this->hasPermission(substr($permissie, 1), $token_authorizable)) {
			return true;
		}

		# Normale permissie:
		# ga alleen verder als er een geldige permissie wordt gevraagd
		if (array_key_exists($permissie, $this->_permissions)) {
			# zoek de code op
			$gevraagd = (int) $this->_permissions[$permissie];

			# $p is de gevraagde permissie als octaal getal
			# de permissies van de gebruiker kunnen we bij $this->_lid opvragen
			# als we die 2 met elkaar AND-en, dan moet het resultaat hetzelfde
			# zijn aan de gevraagde permissie. In dat geval bestaat de permissie
			# van het lid dus minimaal uit de gevraagde permissie
			#
			# voorbeeld:
			#  gevraagd:   P_FORUM_MOD: 0000000700
			#  lid heeft:  R_LID      : 0005544500
			#  AND resultaat          : 0000000500 -> is niet wat gevraagd is -> weiger
			#
			#  gevraagd:  P_DOCS_READ : 0000004000
			#  gebr heeft: R_LID      : 0005544500
			#  AND resultaat          : 0000004000 -> ja!
			$resultaat = $gevraagd & $lidheeft;

			if ($resultaat == $gevraagd) {
				return true;
			}
		}

		//als een uid ingevoerd wordt true teruggeven als het om de huidige gebruiker gaat.
		if ($permissie == $this->getUid()) {
			return true;
			//Behoort een lid tot een bepaalde verticale?
		} elseif (substr($permissie, 0, 9) == 'verticale') {
			$verticale = strtoupper(substr($permissie, 10));
			if (is_numeric($verticale)) {
				if ($verticale == $this->lid->getVerticaleID()) {
					return true;
				}
			} elseif ($verticale == $this->lid->getVerticaleLetter()) {
				return true;
			} elseif ($verticale == strtoupper($this->lid->getVerticale())) {
				return true;
			}
			//Behoort een lid tot een bepaalde (h.t.) groep?
			//als een string als bijvoorbeeld 'pubcie' wordt meegegeven zoekt de ketzer
			//de h.t. groep met die korte naam erbij, als het getal is uiteraard de groep
			//met dat id.
			//met de toevoeging '>Fiscus' kan ook specifieke functie geëist worden binnen een groep
		} elseif (substr($permissie, 0, 5) == 'groep') {
			require_once 'groepen/groep.class.php';
			//splitst opgegeven term in groepsnaam en functie
			$parts = explode(">", substr($permissie, 6), 2);
			try {
				$groep = new OldGroep($parts[0]);
				if ($groep->isLid()) {
					//wordt er een functie gevraagd?
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
				//de groep bestaat niet, we gaan verder.
			}
			//Is een lid man, vrouw en/of geslacht?
		} elseif (substr($permissie, 0, 8) == 'geslacht') {
			$geslacht = strtolower(substr($permissie, 9));
			if ($geslacht == $this->lid->getGeslacht()) {
				return true;
				//we zijn toch zeker niet geslacht??
			} elseif ($geslacht == 'nee' AND $this->hasPermission('P_LOGGED_IN', $token_authorizable)) {
				return true;
			}
			//Behoort een lid tot een bepaalde lichting?
		} elseif (substr($permissie, 0, 7) == 'lidjaar') {
			$lidjaar = substr($permissie, 8);
			if ($lidjaar == $this->lid->getProperty('lidjaar')) {
				return true;
			}
		} elseif (substr($permissie, 0, 8) == 'lichting') {
			$lidjaar = substr($permissie, 9);
			if ($lidjaar == $this->lid->getProperty('lidjaar')) {
				return true;
			}
		} elseif (substr($permissie, 0, 10) == 'Ouderjaars' OR substr($permissie, 0, 10) == 'ouderjaars') {
			if (Lichting::getJongsteLichting() > $this->lid->getProperty('lidjaar') AND $this->hasPermission('P_LOGGED_IN', $token_authorizable)) {
				return true;
			}
		} elseif (substr($permissie, 0, 11) == 'eerstejaars' OR substr($permissie, 0, 11) == 'eerstejaars') {
			if (Lichting::getJongsteLichting() == $this->lid->getProperty('lidjaar') AND $this->hasPermission('P_LOGGED_IN', $token_authorizable)) {
				return true;
			}
		}
		# Zo niet... dan niet
		return false;
	}

	private function _loadPermissions() {
		# Hier staan de permissies die voor enkele onderdelen van
		# de website nodig zijn. Ze worden zowel op de 'echte'
		# website als in het beheergedeelte gebruikt.
		# READ = Rechten om het onderdeel in te zien
		# POST = Rechten om iets toe te voegen
		# MOD  = Moderate rechten, dus verwijderen enzo
		# Let op: de rechten zijn cumulatief en octaal

		$this->_permissions = array(
			'P_NOBODY' => 000000000001,
			'P_LOGGED_IN' => 000000000003, # Leden-menu, eigen profiel raadplegen
			'P_ADMIN' => 000000000007, # Admin dingen algemeen...
			'P_FORUM_READ' => 000000000400, # Forum lezen
			'P_FORUM_POST' => 000000000500, # Berichten plaatsen op het forum en eigen berichten wijzigen
			'P_FORUM_MOD' => 000000000700, # Forum-moderator mag berichten van anderen wijzigen of verwijderen
			'P_DOCS_READ' => 000000004000, # Documenten-rubriek lezen
			'P_DOCS_POST' => 000000005000, # Documenten verwijderen of erbij plaatsen
			'P_DOCS_MOD' => 000000007000, # Documenten aanpassen en fotos uit fotoalbum verwijderen
			'P_PROFIEL_EDIT' => 000000010000, # Eigen gegevens aanpassen
			'P_LEDEN_READ' => 000000040000, # Gegevens over andere leden raadplegen
			'P_LEDEN_EDIT' => 000000020000, # Profiel van andere leden wijzigen
			'P_LEDEN_MOD' => 000070070000, # samengestelde om te kunnen lezen en veranderen bij iedereen
			'P_AGENDA_READ' => 000000100000, # Agenda bekijken
			'P_AGENDA_POST' => 000000300000, # Items toevoegen aan de agenda
			'P_AGENDA_MOD' => 000000700000, # Items beheren in de agenda
			'P_NEWS_POST' => 000001000000, # Nieuws plaatsen en wijzigen van jezelf
			'P_NEWS_MOD' => 000003000000, # Nieuws-moderator mag berichten van anderen wijzigen of verwijderen
			'P_OUDLEDEN_EDIT' => 000020000000, # Profiel van andere leden wijzigen
			'P_OUDLEDEN_READ' => 000040000000, # Gegevens over andere leden raadplegen
			'P_OUDLEDEN_MOD' => 000070070000, # samengestelde om te kunnen lezen en veranderen bij iedereen
			# oudleden-mod is gelijk aan leden-mod
			'P_MAAL_IK' => 000100000000, # kan zich aan en afmelden voor maaltijd en eigen abo wijzigen
			'P_CORVEE_IK' => 000200000000, # kan voorkeuren aangeven voor corveetaken
			'P_CORVEE_MOD' => 000500000000, # mag corveetaken beheren (CorveeCaesar)
			'P_MAAL_MOD' => 000600000000, # mag maaltijden beheren (MaalCie P)
			'P_MAAL_SALDI' => 000700000000, # mag het MaalCie saldo aanpassen van iedereen (MaalCie fiscus)
			'P_MAIL_POST' => 001000000000, # mag berichtjes in de pubciemail rossen
			'P_MAIL_COMPOSE' => 003000000000, # mag alle berichtjes in de pubcie-mail bewerken, en volgorde wijzigen
			'P_MAIL_SEND' => 007000000000, # mag de C.S.R.-mail verzenden
			'P_BIEB_READ' => 000000000010, # Bibliotheek lezen
			'P_BIEB_EDIT' => 000000000030, # Bibliotheek wijzigen
			'P_BIEB_MOD' => 000000000070, # Bibliotheek zowel wijzigen als lezen
			'P_ALLEEN_OUDLID' => 010000000000, # Specifiek voor oudleden
				# N.B. toename van het aantal cijfers is onmogelijk. Een octaal getal moet altijd beginnen met een 0 (dus het meest
				# linker cijfer is niet te gebruiken) en we hebben de maximum waarde van de integer bereikt. Het getal 017777777777
				# is namelijk gelijk aan 2.030.043.135 in het decimale stelsel en de max is 2.147.483.647.
		);

		# Deze waarden worden samengesteld uit bovenstaande permissies en
		# worden in de gebruikersprofielen gebruikt als aanduiding voor
		# welke permissie-groep (Role) de gebruiker in zit.

		$p = $this->_permissions;
		$this->_perm_user = array(
			'R_NOBODY' => $p['P_NOBODY'] | $p['P_FORUM_READ'] | $p['P_AGENDA_READ'],
			'R_LID' => $p['P_LOGGED_IN'] | $p['P_OUDLEDEN_READ'] | $p['P_FORUM_POST'] | $p['P_DOCS_READ'] | $p['P_LEDEN_READ'] | $p['P_PROFIEL_EDIT'] | $p['P_AGENDA_READ'] | $p['P_MAAL_IK'] | $p['P_CORVEE_IK'] | $p['P_MAIL_POST'] | $p['P_BIEB_READ'] | $p['P_NEWS_POST'],
			'R_OUDLID' => $p['P_LOGGED_IN'] | $p['P_LEDEN_READ'] | $p['P_OUDLEDEN_READ'] | $p['P_FORUM_POST'] | $p['P_DOCS_READ'] | $p['P_PROFIEL_EDIT'] | $p['P_FORUM_READ'] | $p['P_MAAL_IK'] | $p['P_CORVEE_IK'] | $p['P_MAIL_POST'] | $p['P_BIEB_READ'] | $p['P_AGENDA_READ'] | $p['P_ALLEEN_OUDLID'],
			'P_MODERATOR' => $p['P_ADMIN'] | $p['P_FORUM_MOD'] | $p['P_DOCS_MOD'] | $p['P_LEDEN_MOD'] | $p['P_OUDLEDEN_MOD'] | $p['P_AGENDA_MOD'] | $p['P_MAAL_IK'] | $p['P_CORVEE_IK'] | $p['P_MAIL_SEND'] | $p['P_NEWS_MOD'] | $p['P_BIEB_MOD']
		);

		# extra dingen, waarvoor de array perm_user zelf nodig is
		$this->_perm_user['R_PUBCIE'] = $this->_perm_user['P_MODERATOR'] | $p['P_MAAL_MOD'] | $p['P_CORVEE_MOD'] | $p['P_MAAL_SALDI'];
		$this->_perm_user['R_MAALCIE'] = $this->_perm_user['R_LID'] | $p['P_MAAL_MOD'] | $p['P_CORVEE_MOD'] | $p['P_MAAL_SALDI'];
		$this->_perm_user['R_BESTUUR'] = $this->_perm_user['R_LID'] | $p['P_LEDEN_MOD'] | $p['P_OUDLEDEN_READ'] | $p['P_NEWS_MOD'] | $p['P_MAAL_MOD'] | $p['P_CORVEE_MOD'] | $p['P_MAAL_SALDI'] | $p['P_MAIL_COMPOSE'] | $p['P_AGENDA_MOD'] | $p['P_FORUM_MOD'] | $p['P_DOCS_MOD'];
		$this->_perm_user['R_VAB'] = $this->_perm_user['R_BESTUUR'] | $p['P_OUDLEDEN_MOD'];
		$this->_perm_user['R_ETER'] = $this->_perm_user['R_NOBODY'] | $p['P_LOGGED_IN'] | $p['P_MAAL_IK'] | $p['P_PROFIEL_EDIT'];
		$this->_perm_user['R_BASF'] = $this->_perm_user['R_LID'] | $p['P_DOCS_MOD'];
	}

	public function isValidPerm($key, $user = true) {
		if (array_key_exists($key, $this->_permissions)) {
			return true;
		}
		if ($user && array_key_exists($key, $this->_perm_user)) {
			return true;
		}
		return false;
	}

	public function getToken($uid = null) {
		if ($uid == null) {
			$uid = $this->getUid();
		}
		$token = substr(md5($uid . getDateTime()), 0, 25);
		$query = "UPDATE lid SET rssToken='" . $token . "' WHERE uid='" . $uid . "' LIMIT 1;";
		if (MySql::instance()->query($query)) {
			LidCache::flushLid($uid);
			return $token;
		} else {
			return false;
		}
	}

	/**
	 * @deprecated Remove after MVC refactor is complete
	 */
	private function logBezoek() {
		$db = MySql::instance();
		$uid = $this->getUid();
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
	 * Maakt een permissiestring met uid's enzo wat leesbaarder.
	 * 
	 * @param string $permission
	 * @return string
	 */
	public static function format($permission) {
		$parts = explode(',', $permission);
		$return = array();
		require_once 'groepen/groep.class.php';
		foreach ($parts as $part) {
			if (Lid::isValidUid($part)) {
				$return[] = (string) LidCache::getLid($part);
			} elseif (substr($part, 0, 5) == 'groep') {
				try {
					$groep = new OldGroep(substr($part, 6));
				} catch (Exception $e) {
					$return[] = 'Onbekende groep';
					continue;
				}
				if ($groep->getId() != 0) {
					$return[] = $groep->getLink();
				}
			} elseif (substr($part, 0, 9) == 'verticale') {
				$verticale = substr($part, 10);
				$namen = Verticale::getNamen();
				$letters = Verticale::getLetters();
				if (isset($namen[$verticale])) {
					$return[] = 'Verticale ' . $namen[$verticale];
				} elseif (in_array(strtoupper($verticale), $letters)) {
					$return[] = 'Verticale ' . $namen[array_search($verticale, $letters)];
				} elseif (in_array($verticale, $namen)) {
					$return[] = 'Verticale ' . $namen[array_search($verticale, $namen)];
				} else {
					$return[] = 'Onbekende verticale';
				}
			} elseif (substr($part, 0, 8) == 'lichting') {
				$return[] = 'Lichting ' . substr($part, 9);
			} elseif (substr($part, 0, 7) == 'lidjaar') {
				$return[] = 'Lichting ' . substr($part, 8);
			} elseif (substr($part, 0, 10) == 'Ouderjaars' OR substr($part, 0, 10) == 'ouderjaars') {
				$return[] = 'Ouderjaars';
			} elseif (substr($part, 0, 11) == 'Eerstejaars' OR substr($part, 0, 11) == 'eerstejaars') {
				$return[] = 'Eerstejaars';
			} elseif (substr($part, 0, 8) == 'geslacht') {
				switch (substr($part, 9)) {
					case 'm':
					case 'man':
						$return[] = 'Man';
						break;
					case 'v':
					case 'vrouw':
						$return[] = 'Vrouw';
						break;
					case 'nee':
						$return[] = 'Niet geslacht';
					default;
						$return[] = 'onbekend geslacht';
				}
			} elseif (substr($part, 0, 7) == 'P_ADMIN') {
				$return[] = 'Admin';
			} elseif (substr($part, 0, 8) == 'P_NOBODY') {
				$return[] = 'Niet-ingelogd';
			} elseif (substr($part, 0, 11) == 'P_LOGGED_IN') {
				$return[] = 'Ingelogd';
			}
		}
		return implode(', ', $return);
	}

}
