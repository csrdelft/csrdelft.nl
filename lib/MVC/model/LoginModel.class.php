<?php

require_once 'lid/lidcache.class.php';
require_once 'MVC/view/Validator.interface.php';

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
		//TODO: parent::__construct();

		if (session_id() == 'deleted') {
			/**
			 * Werkomheen
			 * @source www.nabble.com/problem-with-sessions-in-1.4.8-t2550641.html
			 */
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
		if ($this->loggedinLid->getUid() === 'x999') {
			$token = filter_input(INPUT_GET, 'private_token', FILTER_SANITIZE_STRING);
			if (preg_match('/^[a-z0-9]{25}$/', $token)) {
				$uid = Database::instance()->sqlSelect(array('uid'), 'lid', 'rssToken = ?', array($token), null, null, 1)->fetchColumn();
				$lid = LidCache::getLid($uid);
				if ($lid instanceof Lid) {
					// Subject Assignment:
					$this->loggedinLid = $lid;
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
			$this->loggedinLid = $lid;

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
		$uid = $this->loggedinLid->getUid();
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
		$this->loggedinLid = $lid;
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
		$_SESSION['_suedFrom'] = $this->loggedinLid->getUid();
		$_SESSION['_uid'] = $uid;
		// Subject Assignment:
		$this->loggedinLid = $suNaar;
	}

	public function endSwitchUser() {
		$_SESSION['_uid'] = $_SESSION['_suedFrom'];
		// Subject Assignment:
		$this->loggedinLid = $this->suedFrom;
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
		return LoginModel::instance()->getLid()->getUid();
	}

	public function getLid() {
		return $this->loggedinLid;
	}

	public function isAuthenticatedByToken() {
		return $this->authenticatedByToken;
	}

	public static function mag($permission, $token_authorizable = false, $mandatory_only = false) {
		return AccessModel::instance()->hasPermission(self::instance()->getLid(), $permission, $token_authorizable, $mandatory_only);
	}

}
