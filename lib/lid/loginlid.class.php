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
		if (session_id() == 'deleted') {
			session_regenerate_id();
		}

		// Staat er een gebruiker in de sessie?
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

	/**
	 * Is de huidige gebruiker al actief in een sessie?
	 */
	private function userIsActive() {
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

	/**
	 * dispatch the login proces to a separate function based on MODE
	 * 
	 * @param type $user
	 * @param type $pass
	 * @param type $checkip
	 * @return boolean
	 */
	public function login($user, $pass = '', $checkip = true) {
		switch (constant('MODE')) {
			case 'CLI':
				return $this->_login_cli();
			case 'WEB':
			default:
				return $this->_login_web($user, $pass, $checkip);
		}
	}

	/**
	 * Grant cli access for cron
	 * 
	 * @param type $user
	 * @return boolean
	 */
	private function _login_cli() {
		if (defined('ETC_PATH')) {
			$cred = parse_ini_file(ETC_PATH . 'cron.ini');
		} else {
			$cred = array(
				'user'	 => 'cron',
				'pass'	 => 'pw'
			);
		}
		return $this->_login_web($cred['user'], $cred['pass'], false);
	}

	/**
	 * als een gebruiker wordt ingelogd met ipcheck==true, dan wordt het IP-adres
	 * van de gebruiker opgeslagen in de sessie, en het sessie-cookie zal alleen
	 * vanaf dat adres toegang geven tot de website
	 * 
	 * @param string $user
	 * @param string $pass
	 * @param boolean $checkip
	 * @return boolean
	 */
	private function _login_web($user, $pass, $checkip = true) {
		$lid = false;
		//eerst met uid proberen, komt daar een zinnige gebruiker uit, die gebruiken.
		if (Lid::isValidUid($user)) {
			$lid = LidCache::getLid($user);
		}
		//als er geen lid-object terugkomt, proberen we het met de nickname:
		if (!($lid instanceof Lid)) {
			$lid = Lid::loadByNickname($user);
		}
		//als er geen lid-object terugkomt, proberen we het met de duckname:
		if (!($lid instanceof Lid)) {
			$lid = Lid::loadByDuckname($user);
		}
		//als er geen lid-object terugkomt, haken we af
		if (!($lid instanceof Lid)) {
			return false;
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

		# Split, combine and reverse permission description:

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

		# zoek de rechten van de gebruiker op
		$liddescr = $this->lid->getPermissies();

		//alleen als $token_athorizable true is testen we met de permissies van het
		//geauthenticeerde lid, anders met P_PUBLIC
		if ($this->authenticatedByToken AND ! $token_authorizable) {
			$liddescr = 'P_PUBLIC';
		}

		# ga alleen verder als er een geldige permissie wordt teruggegeven
		if (!array_key_exists($liddescr, $this->_perm_user)) {
			return false;
		}
		# zoek de code op
		$lidheeft = $this->_perm_user[$liddescr];

		# Normale permissie:
		# ga alleen verder als er een geldige permissie wordt gevraagd
		if (array_key_exists($permissie, $this->_permissions)) {
			# zoek de code op
			$gevraagd = $this->_permissions[$permissie];

			# permissies zijn een string, waarin elk kararakter de
			# waarde heeft van een permissielevel voor een bepaald onderdeel.
			#
			# de mogelijke *verschillende* permissies voor een onderdeel zijn machten van twee:
			#  1, 2, 4, 8, etc
			# elk van deze waardes kan onderscheiden worden in een permissie, ook als je ze met elkaar combineert
			# bijv.  3=1+2, 7=1+2+4, 5=1+4, 6=2+4, 12=4+8, etc
			#
			# $gevraagd is de gevraagde permissie als string,
			# de permissies van de gebruiker $lidheeft kunnen we bij $this->lid opvragen
			# als we die 2 met elkaar AND-en, dan moet het resultaat hetzelfde
			# zijn aan de gevraagde permissie. In dat geval bestaat de permissie
			# van het lid dus minimaal uit de gevraagde permissie
			#
			# Bij het AND-en, wordt elke karakter bitwise vergeleken, dat betekent:
			#  - elke karakter van de string omzetten in de ASCII-waarde
			#    (bijv. ?=63, A=65, a=97, etc zie ook http://www.ascii.cl/)
			#  - deze ASCII-waarde omzetten in een binaire getal
			#    (bijv. 2=00010, 4=00100, 5=00101, 14=01110, etc)
			#  - de bits van het binaire getal een-voor-een vergelijken met de bits van het binaire getal uit de
			#    andere string. Als ze overeenkomen worden ze bewaard.
			#    (bijv. 3&5=1 => 00011&00101=00001)
			#
			# voorbeeld (met de getallen 0 tot 7 als ASCII-waardes ipv de symbolen, voor de leesbaarheid)
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
		} elseif (substr($permissie, 0, 11) == 'Eerstejaars' OR substr($permissie, 0, 11) == 'eerstejaars') {
			if (Lichting::getJongsteLichting() == $this->lid->getProperty('lidjaar') AND $this->hasPermission('P_LOGGED_IN', $token_authorizable)) {
				return true;
			}
		}
		# Zo niet... dan niet
		return false;
	}

	private function _loadPermissions() {
		# Hier staan de permissies die voor enkele onderdelen van de website nodig zijn.
		# Ze worden zowel op de 'echte' website als in het beheergedeelte gebruikt.
		#   READ = Rechten om het onderdeel in te zien
		#   POST = Rechten om iets toe te voegen
		#   MOD  = Moderate rechten, dus verwijderen enzo
		# Let op: de rechten zijn cumulatief (bijv: 7=4+2+1, 3=2+1)
		#         als je hiervan afwijkt, kun je (bewust) niveau's uitsluiten (bijv 5=4+1, sluit 2 uit)
		# de levels worden omgezet in een karakter met die ASCII waarde (dit zijn vaak niet-leesbare symbolen, bijv #8=backspace)
		# elke karakter van een string representeert een onderdeel

		$this->_permissions = array(
			'P_PUBLIC'			 => $this->createPermStr(0, 0), # Iedereen op het Internet
			'P_LOGGED_IN'		 => $this->createPermStr(1, 0), # Leden-menu, eigen profiel raadplegen
			'P_PROFIEL_EDIT'	 => $this->createPermStr(1 + 2, 0), # Eigen gegevens aanpassen
			'P_ALLEEN_OUDLID'	 => $this->createPermStr(4, 0), # Specifiek voor oudleden [[let op: niet cumulatief]]
			'P_LEDEN_READ'		 => $this->createPermStr(1, 1), # Gegevens van leden raadplegen
			'P_OUDLEDEN_READ'	 => $this->createPermStr(1 + 2, 1), # Gegevens van oudleden raadplegen
			'P_LEDEN_MOD'		 => $this->createPermStr(1 + 2 + 4, 1), # (Oud)ledengegevens aanpassen
			'P_FORUM_READ'		 => $this->createPermStr(1, 2), # Forum lezen
			'P_FORUM_POST'		 => $this->createPermStr(1 + 2, 2), # Berichten plaatsen op het forum en eigen berichten wijzigen
			'P_FORUM_MOD'		 => $this->createPermStr(1 + 2 + 4, 2), # Forum-moderator mag berichten van anderen wijzigen of verwijderen
			'P_FORUM_BELANGRIJK' => $this->createPermStr(8, 2), # Forum belangrijk (de)markeren  [[let op: niet cumulatief]]
			'P_FORUM_ADMIN'		 => $this->createPermStr(16, 2), # Forum-admin mag deel-fora aanmaken en rechten wijzigen  [[let op: niet cumulatief]]
			'P_AGENDA_READ'		 => $this->createPermStr(1, 3), # Agenda bekijken
			'P_AGENDA_POST'		 => $this->createPermStr(1 + 2, 3), # Items toevoegen aan de agenda
			'P_AGENDA_MOD'		 => $this->createPermStr(1 + 2 + 4, 3), # Items beheren in de agenda
			'P_DOCS_READ'		 => $this->createPermStr(1, 4), # Documenten-rubriek lezen
			'P_DOCS_POST'		 => $this->createPermStr(1 + 2, 4), # Documenten verwijderen of erbij plaatsen en fotos uploaden
			'P_DOCS_MOD'		 => $this->createPermStr(1 + 2 + 4, 4), # Documenten aanpassen en fotos uit fotoalbum verwijderen
			'P_ALBUM_READ'		 => $this->createPermStr(1, 4), # Foto-album bekijken
			'P_ALBUM_DOWN'		 => $this->createPermStr(1 + 2, 4), # Foto-album downloaden
			'P_ALBUM_ADD'		 => $this->createPermStr(1 + 2 + 4, 4), # Fotos uploaden en albums toevoegen
			'P_ALBUM_MOD'		 => $this->createPermStr(1 + 2 + 4 + 8, 4), # Foto-albums aanpassen
			'P_ALBUM_ADMIN'		 => $this->createPermStr(1 + 2 + 4 + 8 + 16, 4), # Fotos uit fotoalbum verwijderen
			'P_BIEB_READ'		 => $this->createPermStr(1, 5), # Bibliotheek lezen
			'P_BIEB_EDIT'		 => $this->createPermStr(1 + 2, 5), # Bibliotheek wijzigen
			'P_BIEB_MOD'		 => $this->createPermStr(1 + 2 + 4, 5), # Bibliotheek zowel wijzigen als lezen
			'P_NEWS_POST'		 => $this->createPermStr(1, 6), # Nieuws plaatsen en wijzigen van jezelf
			'P_NEWS_MOD'		 => $this->createPermStr(1 + 2, 6), # Nieuws-moderator mag berichten van anderen wijzigen of verwijderen
			'P_NEWS_PUBLISH'	 => $this->createPermStr(1 + 2 + 4, 6), # Nieuws publiceren en rechten bepalen
			'P_MAAL_IK'			 => $this->createPermStr(1, 7), # kan zich aan en afmelden voor maaltijd en eigen abo wijzigen
			'P_MAAL_MOD'		 => $this->createPermStr(1 + 2, 7), # mag maaltijden beheren (MaalCie P)
			'P_MAAL_SALDI'		 => $this->createPermStr(1 + 2 + 4, 7), # mag het MaalCie saldo aanpassen van iedereen (MaalCie fiscus)
			'P_CORVEE_IK'		 => $this->createPermStr(1, 8), # kan voorkeuren aangeven voor corveetaken
			'P_CORVEE_MOD'		 => $this->createPermStr(1 + 2, 8), # mag corveetaken beheren (CorveeCaesar)
			'P_CORVEE_SCHED'	 => $this->createPermStr(1 + 2 + 4, 8), # mag de automatische corvee-indeler beheren
			'P_MAIL_POST'		 => $this->createPermStr(1, 9), # mag berichtjes in de courant rossen
			'P_MAIL_COMPOSE'	 => $this->createPermStr(1 + 2, 9), # mag alle berichtjes in de courant bewerken, en volgorde wijzigen
			'P_MAIL_SEND'		 => $this->createPermStr(1 + 2 + 4, 9), # mag de courant verzenden
			'P_ADMIN'			 => $this->createPermStr(1, 10) # Super-admin
		);

		# Deze waarden worden samengesteld uit bovenstaande permissies en
		# worden in de gebruikersprofielen gebruikt als aanduiding voor
		# welke permissie-groep (Role) de gebruiker in zit.
		$p = $this->_permissions;
		$this->_perm_user = array(
			'R_NOBODY'	 => $p['P_PUBLIC'] | $p['P_FORUM_READ'] | $p['P_AGENDA_READ'] | $p['P_ALBUM_READ'],
			'R_LID'		 => $p['P_PROFIEL_EDIT'] | $p['P_OUDLEDEN_READ'] | $p['P_FORUM_POST'] | $p['P_AGENDA_READ'] | $p['P_DOCS_READ'] | $p['P_BIEB_READ'] | $p['P_MAAL_IK'] | $p['P_CORVEE_IK'] | $p['P_MAIL_POST'] | $p['P_NEWS_POST'] | $p['P_ALBUM_MOD']
		);
		$this->_perm_user['R_ETER'] = $this->_perm_user['R_NOBODY'] | $p['P_LOGGED_IN'] | $p['P_PROFIEL_EDIT'] | $p['P_MAAL_IK'];
		$this->_perm_user['R_OUDLID'] = $this->_perm_user['R_LID'] | $p['P_ALLEEN_OUDLID'];
		$this->_perm_user['R_BASF'] = $this->_perm_user['R_LID'] | $p['P_DOCS_MOD'] | $p['P_ALBUM_ADMIN'];
		$this->_perm_user['R_MAALCIE'] = $this->_perm_user['R_LID'] | $p['P_MAAL_MOD'] | $p['P_CORVEE_MOD'] | $p['P_MAAL_SALDI'];
		$this->_perm_user['R_MODERATOR'] = $this->_perm_user['R_LID'] | $p['P_LEDEN_MOD'] | $p['P_FORUM_MOD'] | $p['P_DOCS_MOD'] | $p['P_AGENDA_MOD'] | $p['P_NEWS_MOD'] | $p['P_BIEB_MOD'] | $p['P_MAAL_IK'] | $p['P_CORVEE_IK'] | $p['P_MAIL_COMPOSE'] | $p['P_ALBUM_ADMIN'];
		$this->_perm_user['R_BESTUUR'] = $this->_perm_user['R_MODERATOR'] | $p['P_MAAL_MOD'] | $p['P_CORVEE_MOD'] | $p['P_MAIL_COMPOSE'] | $p['P_FORUM_BELANGRIJK'];
		$this->_perm_user['R_PUBCIE'] = $this->_perm_user['R_MODERATOR'] | $p['P_ADMIN'] | $p['P_CORVEE_SCHED'] | $p['P_MAAL_SALDI'] | $p['P_FORUM_ADMIN'];
	}

	/**
	 * Create permission string with character which has ascii value of request level
	 *
	 * @param int $level           permissiewaarde
	 * @param int $onderdeelnummer starts at zero
	 * @return string permission string
	 */
	private function createPermStr($level, $onderdeelnummer) {
		$nulperm = str_repeat(chr(0), 15);
		return substr_replace($nulperm, chr($level), $onderdeelnummer, 1);
	}

	public function getValidPerms() {
		return array_keys($this->_permissions);
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
			} elseif (substr($part, 0, 8) == 'P_PUBLIC') {
				$return[] = 'Niet-ingelogd';
			} elseif (substr($part, 0, 11) == 'P_LOGGED_IN') {
				$return[] = 'Ingelogd';
			}
		}
		return implode(', ', $return);
	}

}
