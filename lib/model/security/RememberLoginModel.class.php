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

	public function nieuw() {
		$remember = new RememberLogin();
		$remember->uid = LoginModel::getUid();
		$remember->remember_since = getDateTime();
		$remember->device_name = '';
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$remember->ip = $_SERVER['REMOTE_ADDR'];
		} else {
			$remember->ip = '';
		}
		$remember->lock_ip = false;
		return $remember;
	}

	public function rememberLogin(RememberLogin $remember) {
		$rand = crypto_rand_token(255);

		$oldtoken = $remember->token;

		$remember->token = hash('sha512', $rand);
		if ($this->exists($remember)) {
			$this->update($remember);
		} else {
			$remember->id = $this->create($remember);
		}

		setRememberCookie($rand);

		$newtoken = $remember->token;

		$cookietoken = isset($_COOKIE['remember']) ? hash('sha512', $_COOKIE['remember']) : '';
		$headers = implode(", ", headers_list());

		// Doe een log naar de debuglog, om erachter te komen waarom logins verdwijnen.
		DebugLogModel::instance()->log("RememberLoginModel", "rememberLogin", array('$remember'), <<<DUMP
user: {$remember->uid},
id: {$remember->id},
cookietoken: {$cookietoken},
oldtoken: {$oldtoken},
newtoken: {$newtoken},
headers: {$headers}
DUMP
		);

		return;
	}

}
