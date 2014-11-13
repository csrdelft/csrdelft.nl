<?php

/**
 * VerifyModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Model voor two-step verification.
 * 
 */
class VerifyModel extends PersistenceModel implements Validator {

	const orm = 'OneTimeToken';

	protected static $instance;
	/**
	 * Error 
	 * @var string
	 */
	private $error;

	public function validate($uid, $tokenValue) {
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

}

class TimeoutModel extends PersistenceModel {

	const orm = 'VerifyTimeout';

	protected static $instance;

	public function moetWachten($uid) {
		$timeout = $this->retrieveByPrimaryKey(array($uid));
		if ($timeout) {
			$diff = time() - strtotime($timeout->last_try) + 10 * pow(2, $timeout->count - 1);
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

}
