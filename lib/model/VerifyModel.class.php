<?php

/**
 * VerifyModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Model voor two-step verification.
 * 
 */
class VerifyModel extends PersistenceModel {

	const orm = 'OneTimeToken';

	protected static $instance;
	/**
	 * Error 
	 * @var string
	 */
	private $error;

	public function verifyToken($uid, $tokenValue) {
		// check timeout
		$timeout = TimeoutModel::instance()->moetWachten($uid);
		if ($timeout > 0) {
			$this->error = 'Wacht ' . $timeout . ' seconden';
			return false;
		}
		// check token
		$token = $this->find('uid = ? AND token = ?', array($uid, $tokenValue), null, null, 1)->fetch();
		if ($token) {
			// expired?
			if (time() < strtotime($token->expire)) {
				$token->verified = true;
				$_SESSION['_verifiedUid'] = $token->uid;
				$this->update($token);
				TimeoutModel::instance()->goed($token->uid);
				redirect($token->url);
			} else {
				$this->error = 'Token expired';
				$this->delete($token);
			}
		} else {
			$this->error = 'Token invalid';
		}
		unset($_SESSION['_verifiedUid']);
		TimeoutModel::instance()->fout($uid);
		return false;
	}

	public function getError() {
		return $this->error;
	}

	public function isVerified($uid, $url) {
		$token = $this->retrieveByPrimaryKey(array($uid, $url));
		if ($token) {
			if (time() < strtotime($token->expire)) {
				return $token->verified;
			}
		}
		return false;
	}

	public function discardToken($uid, $url) {
		unset($_SESSION['_verifiedUid']);
		$this->deleteByPrimaryKey(array($uid, $url));
	}

	public function createToken($uid, $url, $expire = '+1 hour') {
		$token = new OneTimeToken();
		$token->uid = $uid;
		$token->url = $url;
		$token->token = crypto_rand_token(200);
		$token->expire = getDateTime(strtotime($expire));
		$token->verified = false;
		if ($this->exists($token)) {
			$this->update($token);
		} else {
			$this->create($token);
		}
		return $token;
	}

}

class TimeoutModel extends PersistenceModel {

	const orm = 'VerifyTimeout';

	protected static $instance;

	public function moetWachten($uid) {
		$timeout = $this->retrieveByPrimaryKey(array($uid));
		if ($timeout) {
			$diff = strtotime($timeout->last_try) + 10 * pow(2, $timeout->count - 1) - time();
			if ($diff > 0) {
				return $diff;
			}
		}
		return 0;
	}

	public function fout($uid) {
		$timeout = $this->retrieveByPrimaryKey(array($uid));
		if ($timeout) {
			$timeout->count++;
			$timeout->last_try = getDateTime();
			$this->update($timeout);
			return true;
		} else {
			$timeout = new VerifyTimeout();
			$timeout->uid = $uid;
			$timeout->count = 1;
			$timeout->last_try = getDateTime();
			$this->create($timeout);
			return true;
		}
	}

	public function goed($uid) {
		$timeout = $this->retrieveByPrimaryKey(array($uid));
		if ($timeout) {
			$this->delete($timeout);
		}
	}

}
