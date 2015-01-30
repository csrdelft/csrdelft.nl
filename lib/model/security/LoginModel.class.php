<?php

require_once 'view/Validator.interface.php';
require_once 'model/security/RememberLoginModel.class.php';
require_once 'model/security/AccountModel.class.php';
require_once 'model/ProfielModel.class.php';

/**
 * LoginModel.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Model van het huidige ingeloggede account voor inloggen, uitloggen, su'en etc.
 * 
 * @see AccountModel.class.php
 */
class LoginModel extends PersistenceModel implements Validator {

	const orm = 'LoginSession';

	protected static $instance;

	public static function getUid() {
		return $_SESSION['_uid'];
	}

	public static function getSuedFrom() {
		return AccountModel::get($_SESSION['_suedFrom']);
	}

	public static function getAccount() {
		return AccountModel::get(self::getUid());
	}

	public static function getProfiel() {
		return ProfielModel::get(self::getUid());
	}

	public static function mag($permission, $allowAuthByToken = false) {
		return AccessModel::mag(self::getAccount(), $permission, $allowAuthByToken);
	}

	protected function __construct() {
		parent::__construct('security/');
		/**
		 * Sessie valideren: is er iemand ingelogd en zo ja, is alles ok?
		 * Zo nee, dan public gebruiker er in gooien.
		 */
		if (!$this->validate()) {
			// Subject assignment:
			$_SESSION['_uid'] = 'x999';

			if (MODE === 'CLI') {
				die('access denied');
			}

			// Remember login
			if (isset($_COOKIE['remember'])) {
				$remember = RememberLoginModel::instance()->verifyToken($_SERVER['REMOTE_ADDR'], $_COOKIE['remember']);
				if ($remember) {
					$this->login($remember->uid, null, $remember, $remember->lock_ip);
				}
			} else {
				/**
				 * Als we x999 zijn checken we of er misschien een private token in de $_GET staat.
				 * Deze staat toe zonder wachtwoord gelimiteerde rechten te krijgen op iemands naam.
				 */
				$token = filter_input(INPUT_GET, 'private_token', FILTER_SANITIZE_STRING);
				if (preg_match('/^[a-zA-Z0-9]{150}$/', $token)) {
					$account = AccountModel::instance()->find('private_token = ?', array($token), null, null, 1)->fetch();
					$this->login($account->uid, null, null, true, true, getDateTime());
				}
			}
		}
		if (!self::getAccount()) {
			// public gebruiker stuk?
			header('Retry-After: 3600');
			http_response_code(503);
			die('<h1>503 Service Unavailable</h1>');
		}
	}

	/**
	 * Is de huidige gebruiker al actief in een sessie?
	 */
	public function validate() {
		// Er is geen _uid gezet in $_SESSION dus er is nog niemand ingelogd
		if (!isset($_SESSION['_uid'])) {
			return false;
		}
		// Public gebruiker vereist geen authenticatie
		if ($_SESSION['_uid'] === 'x999') {
			return true;
		}
		// Controleer of sessie niet gesloten is door gebruiker
		$session = $this->retrieveByPrimaryKey(array(hash('sha512', session_id())));
		if (!$session) {
			return false;
		}
		// Controleer of sessie is verlopen
		if ($session->expire AND strtotime($session->expire) <= time()) {
			return false;
		}
		// Controleer gekoppeld ip
		if ($session->lock_ip AND $session->ip !== $_SERVER['REMOTE_ADDR']) {
			return false;
		}
		// Controleer switch user status
		if (isset($_SESSION['_suedFrom'])) {
			$suedFrom = self::getSuedFrom();
			if (!$suedFrom OR $session->uid !== $suedFrom->uid) {
				return false;
			}
			// Controleer of account bestaat
			if (!self::getAccount()) {
				return false;
			}
			return true;
		}
		// Controleer of sessie van gebruiker is
		$account = self::getAccount();
		if (!$account OR $session->uid !== $account->uid) {
			return false;
		}
		// Controleer of wachtwoord is verlopen
		$pass_since = strtotime($account->pass_since);
		$verloop_na = strtotime(Instellingen::get('beveiliging', 'wachtwoorden_verlopen_ouder_dan'));
		$waarschuwing_vooraf = strtotime(Instellingen::get('beveiliging', 'wachtwoorden_verlopen_waarschuwing_vooraf'), $verloop_na);
		if ($pass_since < $verloop_na) {
			if (!startsWith(REQUEST_URI, '/wachtwoord') AND ! startsWith(REQUEST_URI, '/tools/css.php') AND ! startsWith(REQUEST_URI, '/tools/js.php') AND REQUEST_URI !== '/endsu') {
				setMelding('Uw wachtwoord is verlopen', 2);
				redirect('/wachtwoord/verlopen');
			}
		} elseif (REQUEST_URI == '' AND $pass_since < $waarschuwing_vooraf) {
			$uren = ($waarschuwing_vooraf - $pass_since) / 3600;
			if ($uren < 24) {
				setMelding('Uw wachtwoord verloopt binnen ' . $uren . ' uur', 2);
			} else {
				$dagen = floor((double) $uren / (double) 24) . ' dag';
				if ($dagen > 1) {
					$dagen .= 'en';
				}
				setMelding('Uw wachtwoord verloopt over ' . $dagen, 2);
			}
		}
		return true;
	}

	public function hasError() {
		return isset($_SESSION['auth_error']);
	}

	/**
	 * Na opvragen resetten.
	 * 
	 * @return mixed null or string
	 */
	public function getError() {
		if (!$this->hasError()) {
			return null;
		}
		$error = $_SESSION['auth_error'];
		unset($_SESSION['auth_error']);
		return $error;
	}

	public function logBezoek() {
		$db = MijnSqli::instance();
		if (isset($_SESSION['_suedFrom'])) {
			$uid = $_SESSION['_suedFrom'];
		} else {
			$uid = $_SESSION['_uid'];
		}
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
			INSERT INTO log (uid, ip, locatie, moment, url, referer, useragent)
			VALUES ('" . $uid . "', '" . $ip . "', '" . $locatie . "', '" . $datumtijd . "', '" . $url . "', '" . $referer . "', '" . $agent . "')
		;";
		if (!preg_match('/stats.php/', $url) AND $ip !== '0.0.0.0') {
			$db->query($sLogQuery);
		}
	}

	/**
	 * Dispatch the login proces to a separate function based on MODE.
	 * 
	 * Als een gebruiker wordt ingelogd met $lockIP == true, dan wordt het IP-adres
	 * van de gebruiker opgeslagen in de sessie, en het sessie-cookie zal ALLEEN
	 * vanaf dat adres toegang geven tot de website.
	 * 
	 * Als een gebruiker wordt ingelogd met $expire == DateTime, dan verloopt de sessie
	 * van de gebruiker op het gegeven moment en wordt de gebruiker uigelogd.
	 * 
	 * Als een gebruiker wordt ingelogd met $tokenAuthenticated == true, dan wordt het wachtwoord
	 * van de gebruiker NIET gecontroleerd en wordt er ook GEEN timeout geforceerd, er wordt
	 * vanuit gegaan dat VOORAF een token is gecontroleerd en dat voldoende is voor authenticatie.
	 * 
	 * @param string $user
	 * @param string $pass_plain
	 * @param RememberLogin $remember
	 * @param boolean $lockIP
	 * @param boolean $tokenAuthenticated
	 * @param string $expire
	 * @return boolean
	 */
	public function login($user, $pass_plain, RememberLogin $remember = null, $lockIP = false, $tokenAuthenticated = false, $expire = null) {

		if (MODE === 'CLI') {
			if (defined('ETC_PATH')) {
				$cred = parse_ini_file(ETC_PATH . 'cron.ini');
			} else {
				$cred = array(
					'user'	 => 'cron',
					'pass'	 => 'pw'
				);
			}
			$_SERVER['HTTP_USER_AGENT'] = 'CLI';
			$user = $cred['user'];
			$pass_plain = $cred['pass'];
		}

		$user = filter_var($user, FILTER_SANITIZE_STRING);
		$pass_plain = filter_var($pass_plain, FILTER_SANITIZE_STRING);

		// Inloggen met lidnummer of gebruikersnaam
		if (AccountModel::isValidUid($user)) {
			$account = AccountModel::get($user);
		} else {
			$account = AccountModel::instance()->find('username = ?', array($user), null, null, 1)->fetch();
		}

		// Onbekende gebruiker
		if (!$account) {
			$_SESSION['auth_error'] = 'Inloggen niet geslaagd';
			return false;
		}

		// Clear session
		session_unset();

		if (MODE === 'CLI') {
			// no checks
		}
		// Autologin
		elseif ($remember) {
			$_SESSION['_authByCookie'] = true;
		}
		// Previously(!) verified private token or OneTimeToken
		elseif ($tokenAuthenticated) {
			$_SESSION['_authByToken'] = true;
		} else {

			// Check timeout
			$timeout = AccountModel::instance()->moetWachten($account);
			if ($timeout > 0) {
				$_SESSION['auth_error'] = 'Wacht ' . $timeout . ' seconden';
				return false;
			}

			// Check password
			if (AccountModel::instance()->controleerWachtwoord($account, $pass_plain)) {
				AccountModel::instance()->successfulLoginAttempt($account);
			}
			// Wrong password
			else {
				// Password deleted (by admin)
				if ($account->pass_hash == '') {
					$_SESSION['auth_error'] = 'Gebruik wachtwoord vergeten of mail de PubCie';
				}
				// Regular failed username+password
				else {
					$_SESSION['auth_error'] = 'Inloggen niet geslaagd';
					AccountModel::instance()->failedLoginAttempt($account);
				}
				return false;
			}
		}

		// Subject assignment:
		$_SESSION['_uid'] = $account->uid;

		if ($account->uid !== 'x999') {
			// Permissions change: delete old session
			session_regenerate_id(true);

			// Login sessie aanmaken in database
			$session = new LoginSession();
			$session->session_hash = hash('sha512', session_id());
			$session->uid = $account->uid;
			$session->login_moment = getDateTime();
			$session->expire = $expire ? $expire : getDateTime(time() + (int) Instellingen::get('beveiliging', 'session_lifetime_seconds'));
			$session->user_agent = filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_STRING);
			$session->ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_SANITIZE_STRING);
			$session->lock_ip = $lockIP; // sessie koppelen aan ip?
			if ($this->exists($session)) {
				$this->update($session);
			} else {
				$this->create($session);
			}
			if (MODE === 'CLI') {
				return true;
			}

			if ($remember) {
				setMelding('Welkom ' . ProfielModel::getNaam($account->uid, 'civitas') . '! U bent <a href="/instellingen#lidinstellingenform-tab-Beveiliging" style="text-decoration: underline;">automatisch ingelogd</a>.', 0);
			} elseif (!$tokenAuthenticated) {

				// Controleer actief wachtwoordbeleid
				$_POST['checkpw_new'] = $pass_plain;
				$_POST['checkpw_confirm'] = $pass_plain;
				$field = new WachtwoordWijzigenField('checkpw', $account, false); // fetches POST values itself
				if (!$field->validate()) {
					$_SESSION['password_unsafe'] = true;
					setMelding('Uw wachtwoord is onveilig: ' . str_replace('nieuwe', 'huidige', $field->getError()), 2);
					redirect('/wachtwoord/wijzigen');
				}

				// Welcome message
				//setMelding('Welkom ' . ProfielModel::getNaam($account->uid, 'civitas') . '! U bent momenteel <a href="/instellingen#lidinstellingenform-tab-Beveiliging" style="text-decoration: underline;">' . $this->count('uid = ?', array($account->uid)) . 'x ingelogd</a>.', 0);
			}
		}
		return true;
	}

	public function logout() {
		// Forget autologin
		if (isset($_COOKIE['remember'])) {
			$remember = RememberLoginModel::instance()->find('token = ?', array(hash('sha512', $_COOKIE['remember'])), null, null, 1)->fetch();
			if ($remember) {
				RememberLoginModel::instance()->delete($remember);
			}
		}
		// Destroy login session
		$this->deleteByPrimaryKey(array(hash('sha512', session_id())));
		session_destroy();
	}

	public function switchUser($uid) {
		if ($this->isSued()) {
			throw new Exception('Geneste su niet mogelijk!');
		}
		$suNaar = AccountModel::get($uid);
		if (!$this->maySuTo($suNaar)) {
			throw new Exception('Deze gebruiker mag niet inloggen!');
		}
		$suedFrom = self::getAccount();

		// Clear session
		session_unset();

		// Subject assignment:
		$_SESSION['_suedFrom'] = $suedFrom->uid;
		$_SESSION['_uid'] = $suNaar->uid;
	}

	public function endSwitchUser() {
		$suedFrom = self::getSuedFrom();

		// Clear session
		session_unset();

		// Subject assignment:
		$_SESSION['_uid'] = $suedFrom->uid;
		$_SESSION['_suedFrom'] = null;
	}

	public function isSued() {
		if (!isset($_SESSION['_suedFrom'])) {
			return false;
		}
		$suedFrom = self::getSuedFrom();
		return $suedFrom AND AccessModel::mag($suedFrom, 'P_ADMIN');
	}

	public function maySuTo(Account $suNaar) {
		return !$this->isSued() AND $suNaar->uid !== self::getUid() AND AccessModel::mag($suNaar, 'P_LOGGED_IN');
	}

	public function isLoggedIn($allowAuthByToken = false) {
		if (!isset($_SESSION['_uid'])) {
			return false;
		}
		$account = self::getAccount();
		return $account AND AccessModel::mag($account, 'P_LOGGED_IN', $allowAuthByToken);
	}

	/**
	 * Is de huidige gebruiker is geauthenticeerd door middel van een token in de url?
	 * Permissies worden hierdoor beperkt voor de veiligheid.
	 * @see AccessModel::mag()
	 */
	public function isAuthenticatedByToken() {
		return isset($_SESSION['_authByToken']);
	}

	public function isPauper() {
		return isset($_SESSION['pauper']);
	}

	public function setPauper($value) {
		if ($value) {
			$_SESSION['pauper'] = true;
		} else {
			unset($_SESSION['pauper']);
		}
	}

	public function opschonen() {
		foreach ($this->find('expire <= ?', array(getDateTime())) as $session) {
			$this->delete($session);
		}
	}

}
