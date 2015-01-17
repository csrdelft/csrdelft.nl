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
		return $remember;
	}

	public function forgetLogin($tokenString) {
		$this->deleteByPrimaryKey(array($tokenString));
	}

	public function rememberLogin($device_name, $lock_ip) {
		$remember = new RememberLogin();
		$remember->token = crypto_rand_token(255); // password equivalent: should be hashed
		$remember->uid = LoginModel::getUid();
		$remember->remember_since = getDateTime();
		$remember->device_name = $device_name;
		$remember->ip = $_SERVER['REMOTE_ADDR'];
		$remember->lock_ip = $lock_ip;
		$this->create($remember);
		return setcookie('remember', $remember->token, time() + (int) Instellingen::get('beveiliging', 'remember_login_seconds'), '/', 'csrdelft.nl', true, true);
	}

}
