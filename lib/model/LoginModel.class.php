<?php

require_once 'view/Validator.interface.php';
require_once 'model/VerifyModel.class.php';
require_once 'lid/lidcache.class.php';

/**
 * LoginModel.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Model van het huidige ingeloggede lid voor inloggen, uitloggen, su'en etc.
 * 
 */
class LoginModel extends PersistenceModel implements Validator {

	const orm = 'LoginSession';

	protected static $instance;

	public static function getUid() {
		return self::instance()->getLid()->getUid();
	}

	public static function mag($permission, $token_authorizable = false) {
		return AccessModel::mag(self::instance()->getLid(), $permission, $token_authorizable);
	}

	/**
	 * Lid dat op dit moment is ingelogd.
	 */
	private $loggedinLid;
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
		parent::__construct();
		/**
		 * Sessie valideren: is er iemand ingelogd en zo ja, is alles ok?
		 * Zo nee, dan public gebruiker er in gooien.
		 */
		if (!$this->validate() AND ! $this->login('x999', 'x999')) {
			// public gebruiker is stuk
			die('Not accessible');
		}
		if ($this->getLid()->getUid() === 'x999') {
			/**
			 * Als we x999 zijn checken we of er misschien een validatietoken in de $_GET staat.
			 * Een token staat toe zonder wachtwoord gelimiteerde rechten te krijgen op iemands naam.
			 */
			$token = filter_input(INPUT_GET, 'private_token', FILTER_SANITIZE_STRING);
			if (preg_match('/^[a-zA-Z0-9]{150}$/', $token)) {
				$uid = Database::instance()->sqlSelect(array('uid'), 'lid', 'rssToken = ?', array($token), null, null, 1)->fetchColumn();
				$this->login($uid, null, true);
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

		$lid = LidCache::getLid($_SESSION['_uid']);
		if ($lid instanceof Lid) {

			// Check login session
			$session = $this->retrieveByPrimaryKey(array(session_id()));
			if (!$session) {
				return false;
			}
			// Controleer consistentie van browser:
			elseif ($session->user_agent != $_SERVER['HTTP_USER_AGENT']) {
				return false;
			}
			// Controleer gekoppeld ip:
			elseif (isset($session->ip) AND $session->ip != $_SERVER['REMOTE_ADDR']) {
				return false;
			}
			// Controleer switch user status:
			elseif (isset($_SESSION['_suedFrom'])) {
				$this->suedFrom = LidCache::getLid($_SESSION['_suedFrom']);
				if (!$this->suedFrom instanceof Lid OR $session->uid != $this->suedFrom->getUid()) {
					return false;
				}
			}
			// Controleer consistentie van ingelogd lid:
			elseif ($session->uid != $lid->getUid()) {
				return false;
			}

			// Subject Assignment:
			$this->setLid($lid);
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
		$uid = $this->getLid()->getUid();
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
	 * Als een gebruiker wordt ingelogd met tokenOK==true, dan wordt het wachtwoord
	 * van de gebruiker NIET gecontroleerd, en wordt er vanuit gegaan dat een VOORAF
	 * gecontroleerd token voldoende is voor authenticatie.
	 * 
	 * Als een gebruiker wordt ingelogd met lockIP==true, dan wordt het IP-adres
	 * van de gebruiker opgeslagen in de sessie, en het sessie-cookie zal ALLEEN
	 * vanaf dat adres toegang geven tot de website.
	 * 
	 * @param string $uid
	 * @param string $pass
	 * @param boolean $tokenOK
	 * @param boolean $lockIP
	 * @return boolean
	 */
	public function login($uid, $pass, $tokenOK = false, $lockIP = false) {
		$uid = filter_var($uid, FILTER_SANITIZE_STRING);
		$pass = filter_var($pass, FILTER_SANITIZE_STRING);
		switch (constant('MODE')) {
			case 'CLI':
				return $this->loginCli();
			case 'WEB':
			default:
				return $this->loginWeb($uid, $pass, (boolean) $tokenOK, (boolean) $lockIP);
		}
	}

	private function loginCli() {
		if (defined('ETC_PATH')) {
			$cred = parse_ini_file(ETC_PATH . 'cron.ini');
		} else {
			$cred = array(
				'user'	 => 'cron',
				'pass'	 => 'pw'
			);
		}
		$_SERVER['HTTP_USER_AGENT'] = 'CLI';
		return $this->loginWeb($cred['user'], $cred['pass'], false, false);
	}

	private function loginWeb($uid, $pass, $tokenOK = false, $lockIP = false) {
		$lid = false;

		// eerst met uid proberen, komt daar een zinnige gebruiker uit, die gebruiken.
		if (Lid::isValidUid($uid)) {
			$lid = LidCache::getLid($uid);
		}
		// als er geen lid-object terugkomt, proberen we het met de nickname:
		if (!$lid instanceof Lid) {
			$lid = Lid::loadByNickname($uid);
		}

		// check timeout
		if ($lid instanceof Lid) {
			$uid = $lid->getUid();
		} else {
			$uid = 'x999';
		}
		$timeout = TimeoutModel::instance()->moetWachten($uid);
		if ($timeout > 0) {
			$_SESSION['auth_error'] = 'Wacht ' . $timeout . ' seconden';
			return false;
		}

		// als we een gebruiker hebben gevonden controleren we
		// of het wachtwoord klopt
		// of dat er eerder een token is gecontroleerd
		if ($lid instanceof Lid AND ( $tokenOK OR checkpw($lid, $pass) )) {
			TimeoutModel::instance()->goed($lid->getUid());
		} else {
			$_SESSION['auth_error'] = 'Inloggen niet geslaagd<br><a href="/wachtwoord/vergeten">Wachtwoord vergeten?</a>';
			TimeoutModel::instance()->fout($uid);
			return false;
		}

		// Subject Assignment:
		$this->setLid($lid);
		$this->authenticatedByToken = (boolean) $tokenOK;

		if ($uid != 'x999') {
			// Permissions change: delete old session
			session_regenerate_id(true);

			// Login sessie aanmaken in database
			$session = new LoginSession();
			$session->session_id = session_id();
			$session->uid = $lid->getUid();
			$session->login_moment = getDateTime();
			$session->user_agent = filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_STRING);
			$session->ip = $lockIP ? filter_var($_SERVER['REMOTE_ADDR'], FILTER_SANITIZE_STRING) : null; // sessie koppelen aan ip?
			if ($this->exists($session)) {
				$this->update($session);
			} else {
				$this->create($session);
			}
		}
		return true;
	}

	public function logout() {
		$this->deleteByPrimaryKey(array(session_id()));
		session_destroy();
	}

	public function switchUser($uid) {
		if (!Lid::isValidUid($uid)) {
			throw new Exception('Invalid UID');
		}
		if ($this->isSued()) {
			throw new Exception('Geneste su niet mogelijk!');
		}
		if ($this->getUid() === $uid) {
			throw new Exception('Dit ben je zelf!');
		}
		$suNaar = LidCache::getLid($uid);
		if ($suNaar instanceof Lid AND AccessModel::mag($suNaar, 'P_LOGGED_IN')) {
			// Clear session
			session_unset();

			$this->suedFrom = $this->getLid();
			// Subject Assignment:
			$this->setLid($suNaar);

			// Configure session
			$_SESSION['_suedFrom'] = $this->suedFrom->getUid();
		} else {
			throw new Exception('Kan niet switchen naar gebruiker: ' . htmlspecialchars($uid) . '');
		}
	}

	public function endSwitchUser() {
		// Clear session
		session_unset();

		// Subject Assignment:
		$this->setLid($this->suedFrom);
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

	public function getLid() {
		return $this->loggedinLid;
	}

	private function setLid(Lid $lid) {
		$this->loggedinLid = $lid;

		// Configure session
		$_SESSION['_uid'] = $lid->getUid();
	}

	public function isAuthenticatedByToken() {
		return $this->authenticatedByToken;
	}

}
