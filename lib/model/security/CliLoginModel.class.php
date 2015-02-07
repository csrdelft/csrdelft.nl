<?php

require_once 'model/security/LoginModel.class.php';

/**
 * CliLoginModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Model van het huidige ingeloggede account in CLI modus.
 * 
 */
class CliLoginModel extends LoginModel {

	private static $uid = 'x999';

	public static function getUid() {
		return self::$uid;
	}

	public static function getSuedFrom() {
		return false;
	}

	protected function __construct() {
		parent::__construct();
		if (!$this->validate()) {
			die('access denied');
		}
	}

	public function validate() {
		if (defined('ETC_PATH')) {
			$cred = parse_ini_file(ETC_PATH . 'cron.ini');
		} else {
			$cred = array(
				'user'	 => 'cron',
				'pass'	 => 'pw'
			);
		}
		return $this->login($cred['user'], $cred['pass']);
	}

	public function hasError() {
		return false;
	}

	public function login($user, $pass_plain, $wachten = true, RememberLogin $remember = null, $lockIP = false, $tokenAuthenticated = false, $expire = null) {
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
			die('Inloggen niet geslaagd');
		}

		// Clear session
		session_unset();

		// Check password
		if (AccountModel::instance()->controleerWachtwoord($account, $pass_plain)) {
			AccountModel::instance()->successfulLoginAttempt($account);
		}
		// Wrong password
		else {
			// Password deleted (by admin)
			if ($account->pass_hash == '') {
				die('Gebruik wachtwoord vergeten of mail de PubCie');
			}
			// Regular failed username+password
			else {
				AccountModel::instance()->failedLoginAttempt($account);
				die('Inloggen niet geslaagd');
			}
		}

		// Subject assignment:
		self::$uid = $account->uid;

		// Permissions change: delete old session
		session_regenerate_id(true);

		// Login sessie aanmaken in database
		$session = new LoginSession();
		$session->session_hash = hash('sha512', session_id());
		$session->uid = $account->uid;
		$session->login_moment = getDateTime();
		$session->expire = getDateTime(time() + (int) Instellingen::get('beveiliging', 'session_lifetime_seconds'));
		$session->user_agent = MODE;
		$session->ip = '';
		$session->lock_ip = true; // sessie koppelen aan ip?
		if ($this->exists($session)) {
			$this->update($session);
		} else {
			$this->create($session);
		}

		return true;
	}

	public function logout() {
		self::$uid = 'x999';
	}

	public function isSued() {
		return false;
	}

	public function isLoggedIn($allowPrivateUrl = false) {
		$account = static::getAccount();
		return $account AND AccessModel::mag($account, 'P_ADMIN');
	}

	public function isAuthenticatedByToken() {
		return false;
	}

	public function isPauper() {
		return false;
	}

}
