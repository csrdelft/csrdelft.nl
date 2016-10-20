<?php

/**
 * OneTimeTokensModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Model voor two-step verification (2SV).
 * 
 */
class OneTimeTokensModel extends PersistenceModel {

	const ORM = 'OneTimeToken';
	const DIR = 'security/';

	protected static $instance;

	/**
	 * Verify a one time token for a user and redirect to the url.
	 *
	 * @param string $uid
	 * @param string $rand
	 * @return boolean
	 */
	public function verifyToken($uid, $rand) {
		$token = $this->find('uid = ? AND verified = FALSE AND expire > NOW() AND token = ?', array($uid, hash('sha512', $rand)), null, null, 1)->fetch();
		if (!$token) {
			return false;
		}
		if (LoginModel::instance()->login($token->uid, null, false, null, true, true, $token->expire)) {
			$token->verified = true;
			$this->update($token);
			redirect($token->url);
		}
		return false;
	}

	/**
	 * Is current session verified by an one time token to execute a certain url on behalf of the given user uid?
	 *
	 * @param string $uid
	 * @param string $url
	 * @return boolean
	 */
	public function isVerified($uid, $url) {
		$token = $this->retrieveByPrimaryKey(array($uid, $url));
		if ($token) {
			return $token->verified AND LoginModel::getUid() === $token->uid AND strtotime($token->expire) > time();
		}
		return false;
	}

	public function discardToken($uid, $url) {
		$this->deleteByPrimaryKey(array($uid, $url));
	}

	public function createToken($uid, $url) {
		$rand = crypto_rand_token(255);
		$token = new OneTimeToken();
		$token->uid = $uid;
		$token->url = $url;
		$token->token = hash('sha512', $rand);
		$token->expire = getDateTime(strtotime(Instellingen::get('beveiliging', 'one_time_token_expire_after')));
		$token->verified = false;
		if ($this->exists($token)) {
			$this->update($token);
		} else {
			$this->create($token);
		}
		return array($rand, $token->expire);
	}

	public function opschonen() {
		foreach ($this->find('expire <= NOW()') as $token) {
			$this->delete($token);
		}
	}

}
