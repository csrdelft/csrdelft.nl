<?php

require_once 'view/Validator.interface.php';
require_once 'model/entity/security/AuthenticationMethod.enum.php';
require_once 'model/security/RememberLoginModel.class.php';
require_once 'model/security/AccountModel.class.php';
require_once 'model/security/OneTimeTokensModel.class.php';
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

	const ORM = 'LoginSession';
	const DIR = 'security/';

	protected static $instance;

	public static function instance() {
		if (!isset(static::$instance)) {
			/**
			 * Dispatch the login proces to a separate class based on MODE.
			 */
			if (MODE === 'CLI') {
				static::$instance = new CliLoginModel();
			} else {
				static::$instance = parent::instance();
			}
		}
		return static::$instance;
	}

	public static function getUid() {
		if (MODE === 'CLI') {
			return CliLoginModel::getUid();
		}
		return $_SESSION['_uid'];
	}

	public static function getSuedFrom() {
		return AccountModel::get($_SESSION['_suedFrom']);
	}

	public static function getAccount() {
		return AccountModel::get(static::getUid());
	}

	public static function getProfiel() {
		return ProfielModel::get(static::getUid());
	}

	public static function mag($permission, array $allowedAuthenticationMethods = null) {
		return AccessModel::mag(static::getAccount(), $permission, $allowedAuthenticationMethods);
	}

	private $current_session;

	protected function __construct() {
		parent::__construct();
		/**
		 * CliLoginModel doet zijn eigen ding.
		 */
		if ($this instanceof CliLoginModel) {
			return;
		}
		/**
		 * Sessie valideren: is er iemand ingelogd en is alles OK?
		 * Zo ja, sessie verlengen.
		 * Zo nee, dan public gebruiker er in gooien.
		 */
		$this->current_session = $this->getCurrentSession();
		if ($this->validate()) {
			// Public gebruiker heeft geen DB sessie
			if ($_SESSION['_uid'] != 'x999') {
				$this->current_session->expire = getDateTime(time() + getSessionMaxLifeTime());
				$this->update($this->current_session);
			}
		} else {
			// Subject assignment:
			$_SESSION['_uid'] = 'x999';

			// Remember login
			if (isset($_COOKIE['remember'])) {
				$remember = RememberLoginModel::instance()->verifyToken($_COOKIE['remember']);
				if ($remember) {
					$this->login($remember->uid, null, false, $remember, $remember->lock_ip);
				}
			} else {
				/**
				 * Als we x999 zijn checken we of er misschien een private token in de $_GET staat.
				 * Deze staat toe zonder wachtwoord gelimiteerde rechten te krijgen op iemands naam.
				 */
				$token = filter_input(INPUT_GET, 'private_token', FILTER_SANITIZE_STRING);
				if (preg_match('/^[a-zA-Z0-9]{150}$/', $token)) {
					$account = AccountModel::instance()->find('private_token = ?', array($token), null, null, 1)->fetch();
					$this->login($account->uid, null, false, null, true, true, getDateTime());
				}
			}
		}
		if (!static::getAccount()) {
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
		if (!$this->current_session) {
			return false;
		}
		// Controleer of sessie is verlopen
		if ($this->current_session->expire AND strtotime($this->current_session->expire) <= time()) {
			return false;
		}
		// Controleer gekoppeld ip
		if ($this->current_session->lock_ip AND $this->current_session->ip !== $_SERVER['REMOTE_ADDR']) {
			return false;
		}
		// Controleer switch user status
		if (isset($_SESSION['_suedFrom'])) {
			$suedFrom = static::getSuedFrom();
			if (!$suedFrom OR $this->current_session->uid !== $suedFrom->uid) {
				return false;
			}
			// Controleer of account bestaat
			if (!static::getAccount()) {
				return false;
			}
			return true;
		}
		// Controleer of sessie van gebruiker is
		$account = static::getAccount();
		if (!$account OR $this->current_session->uid !== $account->uid) {
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
	 * Inloggen met verschillende mogelijkheden:
	 * 
	 * Als een gebruiker wordt ingelogd met $wacht == true, dan wordt gekeken of
	 * er een timeout nodig is vanwege eerdere mislukte inlogpogingen.
	 * 
	 * Als een gebruiker wordt ingelogd met $lockIP == true, dan wordt het IP-adres
	 * van de gebruiker opgeslagen in de sessie, en het sessie-cookie zal ALLEEN
	 * vanaf dat adres toegang geven tot de website.
	 * 
	 * Als een gebruiker wordt ingelogd met $tokenAuthenticated == true, dan wordt het wachtwoord
	 * van de gebruiker NIET gecontroleerd en wordt er ook GEEN timeout geforceerd, er wordt
	 * vanuit gegaan dat VOORAF een token is gecontroleerd en dat voldoende is voor authenticatie.
	 * 
	 * Als een gebruiker wordt ingelogd met $expire == DateTime, dan verloopt de sessie
	 * van de gebruiker op het gegeven moment en wordt de gebruiker uigelogd.
	 * 
	 * @param string $user
	 * @param string $pass_plain
	 * @param boolean $evtWachten
	 * @param RememberLogin $remember
	 * @param boolean $lockIP
	 * @param boolean $alreadyAuthenticatedByUrlToken
	 * @param string $expire
	 * @return boolean
	 */
	public function login($user, $pass_plain, $evtWachten = true, RememberLogin $remember = null, $lockIP = false, $alreadyAuthenticatedByUrlToken = false, $expire = null) {
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

		// Autologin
		if ($remember) {
			$_SESSION['_authenticationMethod'] = AuthenticationMethod::cookie_token;
		}
		// Previously(!) verified private token or OneTimeToken
		elseif ($alreadyAuthenticatedByUrlToken) {
			$_SESSION['_authenticationMethod'] = AuthenticationMethod::url_token;
		} else {
			// Moet eventueel wachten?
			if ($evtWachten) {
				// Check timeout
				$timeout = AccountModel::instance()->moetWachten($account);
				if ($timeout > 0) {
					$_SESSION['auth_error'] = 'Wacht ' . $timeout . ' seconden';
					return false;
				}
			}

			// Check password
			if (AccountModel::instance()->controleerWachtwoord($account, $pass_plain)) {
				AccountModel::instance()->successfulLoginAttempt($account);
				$_SESSION['_authenticationMethod'] = AuthenticationMethod::password_login;
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

			if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
				$user_agent = filter_var($_SERVER['HTTP_USER_AGENT'], FILTER_SANITIZE_STRING);
			} else {
				$user_agent = '';
			}
			if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
				$remote_addr = filter_var($_SERVER['REMOTE_ADDR'], FILTER_SANITIZE_STRING);
			} else {
				$remote_addr = '';
			}

			// Login sessie aanmaken in database
			$this->current_session = new LoginSession();
			$this->current_session->session_hash = hash('sha512', session_id());
			$this->current_session->uid = $account->uid;
			$this->current_session->login_moment = getDateTime();
			$this->current_session->expire = $expire ? $expire : getDateTime(time() + getSessionMaxLifeTime());
			$this->current_session->user_agent = $user_agent;
			$this->current_session->ip = $remote_addr;
			$this->current_session->lock_ip = $lockIP; // sessie koppelen aan ip?
			$this->current_session->authentication_method = $_SESSION['_authenticationMethod'];
			if ($this->exists($this->current_session)) {
				$this->update($this->current_session);
			} else {
				$this->create($this->current_session);
			}

			if ($remember) {
				setMelding('Welkom ' . ProfielModel::getNaam($account->uid, 'civitas') . '! U bent <a href="/instellingen#lidinstellingenform-tab-Beveiliging" style="text-decoration: underline;">automatisch ingelogd</a>.', 0);
			} elseif (!$alreadyAuthenticatedByUrlToken) {

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
				setMelding('Welkom ' . ProfielModel::getNaam($account->uid, 'civitas') . '! U bent momenteel <a href="/instellingen#lidinstellingenform-tab-Beveiliging" style="text-decoration: underline;">' . $this->count('uid = ? AND expire > NOW()', array($account->uid)) . 'x ingelogd</a>.', 0);
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
			setRememberCookie(null);
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
		$suedFrom = static::getAccount();
		// Keep authentication method
		$authMethod = $this->getAuthenticationMethod();

		// Clear session
		session_unset();

		// Subject assignment:
		$_SESSION['_suedFrom'] = $suedFrom->uid;
		$_SESSION['_uid'] = $suNaar->uid;
		$_SESSION['_authenticationMethod'] = $authMethod;
	}

	public function endSwitchUser() {
		$suedFrom = static::getSuedFrom();
		// Keep authentication method
		$authMethod = $this->getAuthenticationMethod();

		// Clear session
		session_unset();

		// Subject assignment:
		$_SESSION['_uid'] = $suedFrom->uid;
		$_SESSION['_suedFrom'] = null;
		$_SESSION['_authenticationMethod'] = $authMethod;
	}

	public function isSued() {
		if (!isset($_SESSION['_suedFrom'])) {
			return false;
		}
		$suedFrom = static::getSuedFrom();
		return $suedFrom AND AccessModel::mag($suedFrom, 'P_ADMIN');
	}

	public function maySuTo(Account $suNaar) {
		return LoginModel::mag('P_ADMIN') AND ! $this->isSued() AND $suNaar->uid !== static::getUid() AND AccessModel::mag($suNaar, 'P_LOGGED_IN');
	}

	protected function getCurrentSession() {
		return $this->retrieveByPrimaryKey(array(hash('sha512', session_id())));
	}

	/**
	 * Indien de huidige gebruiker is geauthenticeerd door middel van een token in de url
	 * worden Permissies hierdoor beperkt voor de veiligheid.
	 * @see AccessModel::mag()
	 * 
	 * @return AuthenticationMethod|null
	 */
	public function getAuthenticationMethod() {
		if (!isset($_SESSION['_authenticationMethod'])) {
			return null;
		}
		$method = $_SESSION['_authenticationMethod'];
		if ($method === AuthenticationMethod::password_login) {
			if ($this->current_session AND $this->current_session->isRecent()) {
				return AuthenticationMethod::recent_password_login;
			}
		}
		return $method;
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
		foreach ($this->find('expire <= ?', array(getDateTime())) as $this->current_session) {
			$this->delete($this->current_session);
		}
	}

}
