<?php

/**
 * RememberLoginModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class RememberLoginModel extends PersistenceModel {

	const ORM = 'RememberLogin';
	const DIR = 'security/';

	protected static $instance;

	public function verifyToken($ip, $rand) {
		$remember = $this->find('token = ? AND (lock_ip = FALSE OR ip = ?)', array(hash('sha512', $rand), $ip), null, null, 1)->fetch();
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
		$remember->lock_ip = false;
		return $remember;
	}

	public function rememberLogin(RememberLogin $remember) {
		$rand = crypto_rand_token(255);
		$remember->token = hash('sha512', $rand);
		if ($this->exists($remember)) {
			$this->update($remember);
		} else {
			$remember->id = $this->create($remember);
		}
		return setRememberCookie($rand);
	}

}
