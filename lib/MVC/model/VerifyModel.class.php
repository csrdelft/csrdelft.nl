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
		$timeout = TimeoutModel::instance()->moetWachten($uid);
		if ($timeout > 0) {
			$this->error = 'Wacht ' . $timeout . ' seconden';
			return false;
		}
		$token = $this->find('uid = ? AND token = ?', array($uid, $tokenValue), null, null, 1);
		if ($token) {
			if (time() < strtotime($token->expire)) {
				$token->verified = true;
				$this->update($token);
				redirect($token->url);
			} else {
				$this->error = 'Token expired';
			}
		} else {
			$this->error = 'Token invalid';
		}
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
		$this->deleteByPrimaryKey(array($uid, $url));
	}

	public function createToken($uid, $url, $expire) {
		$token = new OneTimeToken();
		$token->uid = $uid;
		$token->url = $url;
		$token->token = $this->getToken(255);
		$token->verified = false;
		$token->expire = getDateTime($expire);
	}

	/**
	 * @source http://stackoverflow.com/a/13733588
	 */
	private function getToken($length) {
		$token = '';
		$codeAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$codeAlphabet.= 'abcdefghijklmnopqrstuvwxyz';
		$codeAlphabet.= '0123456789';
		for ($i = 0; $i < $length; $i++) {
			$token .= $codeAlphabet[$this->crypto_rand_secure(0, strlen($codeAlphabet))];
		}
		return $token;
	}

	private function crypto_rand_secure($min, $max) {
		$range = $max - $min;
		if ($range < 0) {
			return $min; // not so random...
		}
		$log = log($range, 2);
		$bytes = (int) ($log / 8) + 1; // length in bytes
		$bits = (int) $log + 1; // length in bits
		$filter = (int) (1 << $bits) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
			$rnd = $rnd & $filter; // discard irrelevant bits
		} while ($rnd >= $range);
		return $min + $rnd;
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
