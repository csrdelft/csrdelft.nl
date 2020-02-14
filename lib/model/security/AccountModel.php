<?php

namespace CsrDelft\model\security;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\security\Account;
use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\Orm\CachedPersistenceModel;

/**
 * AccountModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Wachtwoord en login timeout management.
 */
class AccountModel extends CachedPersistenceModel {

	const ORM = Account::class;
	const PASSWORD_HASH_ALGORITHM = PASSWORD_DEFAULT;

	/**
	 * @param $uid
	 * @return Account|false
	 */
	public static function get($uid) {
		return static::instance()->retrieveByPrimaryKey(array($uid));
	}

	/**
	 * Dit zegt niet in dat een account of profiel ook werkelijk bestaat!
	 * @param $uid
	 * @return bool
	 */
	public static function isValidUid($uid) {
		return is_string($uid) AND preg_match('/^[a-z0-9]{4}$/', $uid);
	}

	/**
	 * @param string $uid
	 *
	 * @return bool
	 */
	public static function existsUid($uid) {
		return static::instance()->existsByPrimaryKey(array($uid));
	}

	/**
	 * @param $email
	 * @return Account|null
	 */
	public function getByEmail($email) {
		if (!$email) {
			return null;
		}

		$accounts = $this->find('email = ?', [$email])->fetchAll();

		if (count($accounts) == 0) {
			return null;
		}

		if (count($accounts) > 1) {
			throw new CsrException("Meerdere accounts gevonden met dit emailadres. " . $email);
		}

		return $accounts[0];
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function existsUsername($name) {
		return $this->database->sqlExists($this->getTableName(), 'username = ?', array($name));
	}

	/**
	 * @param string $uid
	 *
	 * @return Account
	 * @throws CsrGebruikerException
	 */
	public function maakAccount($uid) {
		$profiel = ProfielRepository::get($uid);
		if (!$profiel) {
			throw new CsrGebruikerException('Profiel bestaat niet');
		}
		if (CiviSaldoModel::instance()->find('uid = ?', array($uid))->rowCount() === 0){
			// Maak een CiviSaldo voor dit account
			CiviSaldoModel::instance()->maakSaldo($uid);
		}

		$account = new Account();
		$account->uid = $uid;
		$account->username = $uid;
		$account->email = $profiel->email;
		$account->pass_hash = '';
		$account->pass_since = getDateTime();
		$account->failed_login_attempts = 0;
		$account->perm_role = AccessModel::instance()->getDefaultPermissionRole($profiel->status);
		$this->create($account);
		return $account;
	}

	/**
	 * Verify SSHA hash.
	 *
	 * @param Account $account
	 * @param string $passPlain
	 * @return boolean
	 */
	public function controleerWachtwoord(Account $account, $passPlain) {
		// Controleer of het wachtwoord klopt
		$hash = $account->pass_hash;
		if (startsWith($hash, "{SSHA}")) {
			$valid = $this->checkLegacyPasswordHash($passPlain, $hash);
		} else {
			$valid = password_verify($passPlain, $hash);
		}

		// Rehash wachtwoord als de hash niet aan de eisen voldoet
		if ($valid && password_needs_rehash($hash, AccountModel::PASSWORD_HASH_ALGORITHM)) {
			$this->wijzigWachtwoord($account, $passPlain, false);
		}

		return $valid === true;
	}

	private function checkLegacyPasswordHash($passPlain, $hash) {
		$ohash = base64_decode(substr($hash, 6));
		$osalt = substr($ohash, 20);
		$ohash = substr($ohash, 0, 20);
		$nhash = pack("H*", sha1($passPlain . $osalt));
		return hash_equals($ohash, $nhash);
	}

	/**
	 * Create SSH hash.
	 *
	 * @param string $passPlain
	 * @return string
	 */
	public function maakWachtwoord($passPlain) {
		return password_hash($passPlain, AccountModel::PASSWORD_HASH_ALGORITHM);
	}

	/**
	 * Reset het wachtwoord van de gebruiker.
	 *  - Controleert GEEN eisen aan wachtwoord
	 *  - Wordt NIET gelogged in de changelog van het profiel
	 * @param Account $account
	 * @param $passPlain
	 * @param bool $isVeranderd
	 * @return bool
	 */
	public function wijzigWachtwoord(Account $account, $passPlain, bool $isVeranderd = true) {
		if ($passPlain != '') {
			$account->pass_hash = $this->maakWachtwoord($passPlain);
			if ($isVeranderd) {
				$account->pass_since = getDateTime();
			}
		}
		$this->update($account);

		if ($isVeranderd) {
			// Sync LDAP
			$profiel = $account->getProfiel();
			if ($profiel) {
				$profiel->email = $account->email;
				ContainerFacade::getContainer()->get(ProfielRepository::class)->update($profiel);
			}
		}
		return true;
	}

	/**
	 * @param Account $account
	 */
	public function resetPrivateToken(Account $account) {
		$account->private_token = crypto_rand_token(150);
		$account->private_token_since = getDateTime();
		$this->update($account);
	}

	/**
	 * @param Account $account
	 *
	 * @return int
	 */
	public function moetWachten(Account $account) {
		/**
		 * @source OWASP best-practice
		 */
		switch ($account->failed_login_attempts) {
			case 0:
				$wacht = 0;
				break;
			case 1:
				$wacht = 5;
				break;
			case 2:
				$wacht = 15;
				break;
			default:
				$wacht = 45;
				break;
		}
		$diff = strtotime($account->last_login_attempt) + $wacht - time();
		if ($diff > 0) {
			return $diff;
		}
		return 0;
	}

	/**
	 * @param Account $account
	 */
	public function failedLoginAttempt(Account $account) {
		$account->failed_login_attempts++;
		$account->last_login_attempt = getDateTime();
		$this->update($account);
	}

	/**
	 * @param Account $account
	 */
	public function successfulLoginAttempt(Account $account) {
		$account->failed_login_attempts = 0;
		$account->last_login_attempt = getDateTime();
		$account->last_login_success = getDateTime();
		$this->update($account);
	}

}
