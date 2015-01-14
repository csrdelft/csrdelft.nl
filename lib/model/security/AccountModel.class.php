<?php

/**
 * AccountModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Wachtwoord en login timeout management.
 * 
 */
class AccountModel extends CachedPersistenceModel {

	const orm = 'Account';

	protected static $instance;

	public static function get($uid) {
		return static::instance()->retrieveByPrimaryKey(array($uid));
	}

	/**
	 * Dit zegt niet in dat een account of profiel ook werkelijk bestaat!
	 */
	public static function isValidUid($uid) {
		return is_string($uid) AND preg_match('/^[a-z0-9]{4}$/', $uid);
	}

	public static function existsUid($uid) {
		return static::instance()->existsByPrimaryKey(array($uid));
	}

	public static function existsUsername($name) {
		return Database::sqlExists(static::instance()->orm->getTableName(), 'username = ?', array($name));
	}

	protected function __construct() {
		parent::__construct('security/');
	}

	public function maakAccount($uid) {
		$profiel = ProfielModel::get($uid);
		if (!$profiel) {
			throw new Exception('Profiel bestaat niet');
		}
		$account = new Account();
		$account->uid = $uid;
		$account->username = $uid;
		$account->email = $profiel->email;
		$account->pass_hash = '';
		$account->pass_since = '';
		$account->perm_role = AccessModel::instance()->getDefaultPermissionRole($profiel->status);
		$this->create($account);
		return $account;
	}

	/**
	 * Verify SSHA hash.
	 * 
	 * @param Account $account
	 * @param string $pass_plain
	 * @return boolean
	 */
	public function controleerWachtwoord(Account $account, $pass_plain) {
		$ohash = base64_decode(substr($account->pass_hash, 6));
		$osalt = substr($ohash, 20);
		$ohash = substr($ohash, 0, 20);
		$nhash = pack("H*", sha1($pass_plain . $osalt));
		if ($ohash === $nhash) {
			return true;
		}
		return false;
	}

	/**
	 * Create SSH hash.
	 * 
	 * @param string $pass_plain
	 * @return string
	 */
	public function maakWachtwoord($pass_plain) {
		$salt = mhash_keygen_s2k(MHASH_SHA1, $pass_plain, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
		return "{SSHA}" . base64_encode(mhash(MHASH_SHA1, $pass_plain . $salt) . $salt);
	}

	/**
	 * Reset het wachtwoord van de gebruiker.
	 *  - Controleerd GEEN eisen aan wachtwoord
	 *  - Reset naar random wachtwoord als null
	 *  - Wordt niet gelogged in de changelog van het profiel
	 */
	public function wijzigWachtwoord(Account $account, $pass_plain) {
		// Niet veranderd?
		if ($this->controleerWachtwoord($account, $pass_plain)) {
			return false;
		}
		if ($pass_plain != '') {
			$account->pass_hash = $this->maakWachtwoord($pass_plain);
			$account->pass_since = getDateTime();
		}
		$this->update($account);
		// Sync LDAP
		$profiel = $account->getProfiel();
		if ($profiel) {
			$profiel->email = $account->email;
			ProfielModel::instance()->update($profiel);
		}
		return true;
	}

	public function resetPrivateToken(Account $account) {
		$account->private_token = crypto_rand_token(150);
		$account->private_token_since = getDateTime();
		$this->update($account);
	}

	public function moetWachten(Account $account) {
		$diff = strtotime($account->last_login_attempt) + 10 * pow(2, $account->failed_login_attempts - 3) - time();
		if ($diff > 0) {
			return $diff;
		}
		return 0;
	}

	public function failedLoginAttempt(Account $account) {
		$account->failed_login_attempts++;
		$account->last_login_attempt = getDateTime();
		$this->update($account);
	}

	public function successfulLoginAttempt(Account $account) {
		$account->failed_login_attempts = 0;
		$account->last_login_attempt = getDateTime();
		$account->last_login_success = getDateTime();
		$this->update($account);
	}

}
