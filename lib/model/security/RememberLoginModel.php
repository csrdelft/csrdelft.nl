<?php

namespace CsrDelft\model\security;

use CsrDelft\model\entity\security\RememberLogin;
use CsrDelft\Orm\PersistenceModel;

/**
 * RememberLoginModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 */
class RememberLoginModel extends PersistenceModel {

	const ORM = RememberLogin::class;

	/**
	 * @param string $rand
	 *
	 * @return bool|RememberLogin
	 */
	public function verifyToken($rand) {
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		} else {
			$ip = '';
		}
		$remember = $this->find('token = ? AND (lock_ip = FALSE OR ip = ?)', array(hash('sha512', $rand), $ip), null, null, 1)->fetch();
		if (!$remember) {
			return false;
		}
		$this->rememberLogin($remember);
		return $remember;
	}

	/**
	 * @return RememberLogin
	 */
	public function nieuw() {
		$remember = new RememberLogin();
		$remember->uid = LoginModel::getUid();
		$remember->remember_since = getDateTime();
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$remember->device_name = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$remember->device_name = '';
		}
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$remember->ip = $_SERVER['REMOTE_ADDR'];
		} else {
			$remember->ip = '';
		}
		$remember->lock_ip = false;
		return $remember;
	}

	/**
	 * @param RememberLogin $remember
	 */
	public function rememberLogin(RememberLogin $remember) {
		$rand = crypto_rand_token(255);

		$remember->token = hash('sha512', $rand);
		if ($this->exists($remember)) {
			$this->update($remember);
		} else {
			$remember->id = $this->create($remember);
		}

		setRememberCookie($rand);
	}
}
