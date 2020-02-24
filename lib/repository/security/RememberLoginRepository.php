<?php

namespace CsrDelft\repository\security;

use CsrDelft\entity\security\RememberLogin;
use CsrDelft\model\security\LoginModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @method RememberLogin|null find($id, $lockMode = null, $lockVersion = null)
 * @method RememberLogin|null findOneBy(array $criteria, array $orderBy = null)
 * @method RememberLogin[]    findAll()
 * @method RememberLogin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RememberLoginRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, RememberLogin::class);
	}

	/**
	 * @param string $rand
	 *
	 * @return bool|RememberLogin
	 * @throws NonUniqueResultException
	 */
	public function verifyToken($rand) {
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		} else {
			$ip = '';
		}
		$qb = $this->createQueryBuilder('t');
		$qb->andWhere('t.token = :token');
		$qb->andWhere('t.lock_ip = FALSE or t.ip = :ip');
		$qb->setParameters(['token'=>hash('sha512', $rand), 'ip'=>$ip]);
		try {
			$remember = $qb->getQuery()->getSingleResult();
			$this->rememberLogin($remember);
			return $remember;
		} catch (NoResultException $e) {
			return false;
		} catch (NonUniqueResultException $e) {
			throw $e;
		}
	}

	/**
	 * @param RememberLogin $remember
	 */
	public function rememberLogin(RememberLogin $remember) {
		$rand = crypto_rand_token(255);

		$remember->token = hash('sha512', $rand);
		$this->getEntityManager()->persist($remember);
		$this->getEntityManager()->flush();

		setRememberCookie($rand);
	}

	/**
	 * @return RememberLogin
	 */
	public function nieuw() {
		$remember = new RememberLogin();
		$remember->uid = LoginModel::getUid();
		$remember->remember_since = date_create();
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$remember->device_name = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$remember->device_name = '';
		}
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$remember->ip = $_SERVER['REMOTE_ADDR'];
		} else {
			$remember->ip = '';
		}
		$remember->lock_ip = false;
		return $remember;
	}

	public function retrieveByUuid($UUID) {
		/** @var ClassMetadata $metadata */
		$metadata = $this->getClassMetadata();

		$parts = explode('@', $UUID, 2);
		$primary_key_values = explode('.', $parts[0]);
		return $this->findOneBy(array_combine($metadata->getIdentifierFieldNames(), $primary_key_values));
	}

	public function verwijder($token) {
		$rememberLogin = $this->findOneBy(['token' => $token]);
		$this->getEntityManager()->remove($rememberLogin);
		$this->getEntityManager()->flush();
	}
}
