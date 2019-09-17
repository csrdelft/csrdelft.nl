<?php

namespace CsrDelft\model\security;

use CsrDelft\model\entity\security\Account;
use CsrDelft\model\entity\security\OneTimeToken;
use CsrDelft\Orm\PersistenceModel;

/**
 * OneTimeTokensModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Model voor two-step verification (2SV).
 */
class OneTimeTokensModel extends PersistenceModel {

	const ORM = OneTimeToken::class;

	/**
	 * Verify that the token for the given url is valid. If valid returns the associated account. Otherwise returns null.
	 *
	 * @param string $url
	 * @param string $token
	 * @return Account|null
	 */
	public function verifyToken($url, $token) {
		$token = $this->find('url = ? AND expire > NOW() AND token = ?', array($url, hash('sha512', $token)), null, null, 1)->fetch();
		if (!$token) {
			return null;
		}
		return AccountModel::get($token->uid);
	}

	/**
	 * Is current session verified by an one time token to execute a certain url on behalf of the given user uid?
	 *
	 * @param string $uid
	 * @param string $url
	 * @return boolean
	 */
	public function isVerified($uid, $url) {
		/** @var OneTimeToken $token */
		$token = $this->retrieveByPrimaryKey(array($uid, $url));
		if ($token) {
			return $token->verified AND LoginModel::getUid() === $token->uid AND strtotime($token->expire) > time();
		}
		return false;
	}

	/**
	 * @param string $uid
	 * @param string $url
	 */
	public function discardToken($uid, $url) {
		$this->deleteByPrimaryKey(array($uid, $url));
	}

	/**
	 * @param string $uid
	 * @param string $url
	 *
	 * @return array
	 */
	public function createToken($uid, $url) {
		$rand = crypto_rand_token(255);
		$token = new OneTimeToken();
		$token->uid = $uid;
		$token->url = $url;
		$token->token = hash('sha512', $rand);
		$token->expire = getDateTime(strtotime(instelling('beveiliging', 'one_time_token_expire_after')));
		$token->verified = false;
		if ($this->exists($token)) {
			$this->update($token);
		} else {
			$this->create($token);
		}
		return array($rand, $token->expire);
	}

	/**
	 */
	public function opschonen() {
		foreach ($this->find('expire <= NOW()') as $token) {
			$this->delete($token);
		}
	}

}
