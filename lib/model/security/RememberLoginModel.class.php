<?php

/**
 * RememberLoginModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class RememberLoginModel extends PersistenceModel {

	const orm = 'RememberLogin';

	protected static $instance;

	protected function __construct() {
		parent::__construct('security/');
	}

	public function verifyLogin($ip, $tokenString) {
		$remember = $this->retrieveByPrimaryKey(array($tokenString));
		if (!$remember OR ( $remember->lock_ip AND $ip !== $remember->ip )) {
			return false;
		}
		$this->rememberLogin($remember);
		return $remember;
	}

	public function nieuwRememberLogin() {
		$remember = new RememberLogin();
		$remember->uid = LoginModel::getUid();
		$remember->remember_since = getDateTime();
		$remember->device_name = '';
		$remember->ip = $_SERVER['REMOTE_ADDR'];
		$remember->lock_ip = true;
		return $remember;
	}

	public function rememberLogin(RememberLogin $remember) {
		if ($this->exists($remember)) {
			$this->delete($remember);
		}
		$remember->token = crypto_rand_token(255); // password equivalent: should be hashed
		$this->create($remember);
		return setcookie('remember', $remember->token, time() + (int) Instellingen::get('beveiliging', 'remember_login_seconds'), '/', 'csrdelft.nl', FORCE_HTTPS, true);
	}

	public function forgetLogin($tokenString) {
		$this->deleteByPrimaryKey(array($tokenString));
	}

}
