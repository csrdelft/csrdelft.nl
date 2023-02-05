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
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Account::class);
	}

	/**
	 * Dit zegt niet in dat een account of profiel ook werkelijk bestaat!
	 * @param $uid
	 * @return bool
	 */
	public static function isValidUid($uid)
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

	public function findAdmins()
	{
		return $this->createQueryBuilder('a')
			->where('a.perm_role NOT IN (:admin_perm_roles)')
			->setParameter('admin_perm_roles', [
				AccessRole::Lid,
				AccessRole::Nobody,
				AccessRole::Eter,
				AccessRole::Oudlid,
			])
			->getQuery()
			->getResult();
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

	public function upgradePassword(
		UserInterface $user,
		string $newEncodedPassword
	): void {
		$user->pass_hash = $newEncodedPassword;

		$this->_em->flush();
		$this->_em->clear();
	}

	public function loadUserByUsername(string $username)
	{
		return $this->findOneByUsername($username);
	}

	public function findOneByUsername($username)
	{
		return $this->find($username) ??
			($this->findOneBy(['username' => $username]) ??
				$this->findOneByEmail($username));
	}

	/**
	 * @param $email
	 * @return Account|null
	 */
	public function findOneByEmail($email)
	{
		if (empty($email)) {
			return null;
		}

		return $this->findOneBy(['email' => $email]);
	}
}
