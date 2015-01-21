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

	public static function get($id) {
		return static::instance()->retrieveByPrimaryKey(array($id));
	}

	protected function __construct() {
		parent::__construct('security/');
	}

	public function verifyToken($ip, $tokenString) {
		$remember = $this->find('token = ? AND (lock_ip = FALSE OR ip = ?)', array($tokenString, $ip), null, null, 1)->fetch();
		if (!$remember) {
			return false;
		}
		$this->rememberLogin($remember);
		return $remember;
	}

	public function nieuw() {
		$remember = new RememberLogin();
		$remember->uid = LoginModel::getUid();
		$remember->remember_since = getDateTime();
		$remember->device_name = '';
		$remember->ip = $_SERVER['REMOTE_ADDR'];
		$remember->lock_ip = true;
		return $remember;
	}

	public function rememberLogin(RememberLogin $remember) {
		$remember->token = crypto_rand_token(255); // password equivalent: should be hashed
		if ($this->exists($remember)) {
			$this->update($remember);
		} else {
			$remember->id = $this->create($remember);
		}
		return setcookie('remember', $remember->token, time() + (int) Instellingen::get('beveiliging', 'remember_login_seconds'), '/', 'csrdelft.nl', FORCE_HTTPS, true);
	}

}
