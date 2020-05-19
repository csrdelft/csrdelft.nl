<?php

namespace CsrDelft\repository\security;

use CsrDelft\entity\security\LoginSession;
use CsrDelft\repository\AbstractRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @see AccountRepository
 *
 * @method LoginSession|null find($id, $lockMode = null, $lockVersion = null)
 * @method LoginSession|null findOneBy(array $criteria, array $orderBy = null)
 * @method LoginSession[]    findAll()
 * @method LoginSession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method LoginSession|null retrieveByUuid($UUID)
 */
class LoginSessionRepository extends AbstractRepository {
	/**
	 * LoginModel constructor.
	 * @param ManagerRegistry $registry
	 */
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, LoginSession::class);
	}

	/**
	 * @param LoginSession $loginSession
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function update(LoginSession $loginSession) {
		$this->_em->persist($loginSession);
		$this->_em->flush();
	}

	/**
	 * @param $uid
	 * @return int
	 * @throws NoResultException
	 * @throws NonUniqueResultException
	 */
	public function getActiveSessionCount($uid) {
		return (int)$this->createQueryBuilder('login')
			->select('COUNT(login.session_hash)')
			->where('login.uid = :uid AND login.expire > NOW()')
			->setParameter('uid', $uid)
			->getQuery()->getSingleScalarResult();
	}

	/**
	 */
	public function opschonen() {
		$this->createQueryBuilder('login')
			->delete()
			->where('l.expire <= :nu')
			->setParameter('nu', date_create_immutable())
			->getQuery()->execute();
	}

	public function removeByHash($hash) {
		$this->createQueryBuilder('l')
			->delete()
			->where('l.session_hash = :hash')
			->setParameter('hash', $hash)
			->getQuery()->execute();
	}
}
