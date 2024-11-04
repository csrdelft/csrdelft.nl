<?php

namespace CsrDelft\repository\security;

use CsrDelft\common\Util\CryptoUtil;
use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\enum\AccessRole;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * AccountRepository
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Wachtwoord en login timeout management.
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountRepository extends AbstractRepository implements
	PasswordUpgraderInterface,
	UserLoaderInterface
{


	/**
	 * Dit zegt niet in dat een account of profiel ook werkelijk bestaat!
	 * @param $uid
	 * @return bool
	 */
	public static function isValidUid(string $uid)
	{
		return is_string($uid) && preg_match('/^[a-z0-9]{4}$/', $uid);
	}

	/**
	 * @param string $uid
	 *
	 * @return bool
	 */
	public function existsUid($uid)
	{
		return $this->find($uid) != null;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function existsUsername($name)
	{
		return $this->findOneBy(['username' => $name]) != null;
	}

	/**
	 * @param Account $account
	 */
	public function resetPrivateToken(Account $account)
	{
		$account->private_token = CryptoUtil::crypto_rand_token(150);
		$account->private_token_since = date_create_immutable();
		$this->_em->persist($account);
		$this->_em->flush();
	}

	/**
	 * @param Account $account
	 *
	 * @return int
	 */
	public function moetWachten(Account $account)
	{
		/**
		 * @source OWASP best-practice
		 */
		$wacht = match ($account->failed_login_attempts) {
			0 => 0,
			1 => 5,
			2 => 15,
			default => 45,
		};
		if ($account->last_login_attempt == null) {
			return 0;
		}
		$diff = $account->last_login_attempt->getTimestamp() + $wacht - time();
		if ($diff > 0) {
			return $diff;
		}
		return 0;
	}

	/**
	 * @param Account $account
	 */
	public function failedLoginAttempt(Account $account)
	{
		$account->failed_login_attempts++;
		$account->last_login_attempt = date_create_immutable();
		$this->_em->persist($account);
		$this->_em->flush();
	}

	/**
	 * @param Account $account
	 */
	public function successfulLoginAttempt(Account $account)
	{
		$account->failed_login_attempts = 0;
		$account->last_login_attempt = date_create_immutable();
		$account->last_login_success = date_create_immutable();
		$this->_em->persist($account);
		$this->_em->flush();
	}

	public function delete(Account $account)
	{
		$this->_em->remove($account);
		$this->_em->flush();
	}

	public function loadUserByUsername(string $username): ?UserInterface
	{
		return $this->findOneByUsername($username);
	}

	/**
	 * @param $email
	 * @return Account|null
	 */
	public function findOneByEmail(string $email): ?Account
	{
		if (empty($email)) {
			return null;
		}

		return $this->findOneBy(['email' => $email]);
	}
}
