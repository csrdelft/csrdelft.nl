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

	protected function __construct() {
		parent::__construct('security/');
	}

	public function controleerWachtwoord(Account $account, $pass_plain) {
		// Verify SSHA hash
		$ohash = base64_decode(substr($account->pass_hash, 6));
		$osalt = substr($ohash, 20);
		$ohash = substr($ohash, 0, 20);
		$nhash = pack("H*", sha1($pass_plain . $osalt));
		#echo "ohash: {$ohash}, nhash: {$nhash}";
		if ($ohash === $nhash) {
			return true;
		}
		return false;
	}

	public function maakWachtwoord(Account $account, $pass_plain) {
		$salt = mhash_keygen_s2k(MHASH_SHA1, $pass_plain, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
		return "{SSHA}" . base64_encode(mhash(MHASH_SHA1, $pass_plain . $salt) . $salt);
	}

	/**
	 * Reset het wachtwoord van de gebruiker.
	 *  - Controleerd GEEN eisen aan wachtwoord
	 *  - Reset naar random wachtwoord als null
	 *  - Wordt niet gelogged in de changelog van het profiel
	 */
	public function resetWachtwoord(Account $account, $pass_plain) {
		// Niet veranderd?
		if ($this->controleerWachtwoord($account, $pass_plain)) {
			return false;
		}
		$account->pass_hash = $this->maakWachtwoord($this, $pass_plain);
		$account->pass_since = getDateTime();
		$this->update($account);
		$profiel = $account->getProfiel();
		if ($profiel) {
			try {
				if (!ProfielModel::instance()->save_ldap($profiel)) {
					throw new Exception('LDAP is niet bijgewerkt');
				}
			} catch (Exception $e) {
				setMelding($e->getMessage(), -1);
			}
		}
		return true;
	}

	public function resetPrivateToken(Account $account) {
		$account->private_token = crypto_rand_token(150);
		$account->private_token_since = getDateTime();
		$this->update($account);
	}

	public function moetWachten(Account $account) {
		$diff = strtotime($account->last_login_attempt) + 10 * pow(2, $account->failed_login_attempts - 1) - time();
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

	private function convert() {
		foreach (ProfielModel::instance()->find() as $profiel) {
			$account = new Account();
			$account->uid = $profiel->uid;
			$account->username = $profiel->nickname;
			$account->email = $profiel->email;
			$account->pass_hash = $profiel->password;
			$account->pass_since = getDateTime();
			$account->last_login_success = null;
			$account->last_login_attempt = null;
			$account->failed_login_attempts = 0;
			$account->blocked_reason = null;
			$account->perm_role = $profiel->permissies;
			$account->private_token = null;
			$account->private_token_since = null;
			$this->create($account);
		}
	}

}
