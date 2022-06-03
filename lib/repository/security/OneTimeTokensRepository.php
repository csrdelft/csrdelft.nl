<?php

namespace CsrDelft\repository\security;

use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\OneTimeToken;
use CsrDelft\repository\AbstractRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * OneTimeTokensModel.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Model voor two-step verification (2SV).
 * @method OneTimeToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method OneTimeToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method OneTimeToken[]    findAll()
 * @method OneTimeToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OneTimeTokensRepository extends AbstractRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, OneTimeToken::class);
	}

	public function hasToken($uid, $url) {
		return $this->find(['uid' => $uid, 'url' => $url]) != null;
	}

	/**
	 * Verify that the token for the given url is valid. If valid returns the associated account. Otherwise returns null.
	 *
	 * @param string $url
	 * @param string $token
	 * @return Account|null
	 */
	public function verifyToken($url, $token) {
		$qb = $this->createQueryBuilder('t');
		$qb->andWhere('t.url = :url');
		$qb->andWhere('t.expire > CURRENT_DATE()');
		$qb->andWhere('t.token = :token');
		$qb->setParameters(['url' => $url, 'token' => hash('sha512', $token)]);
		try {
			$tokenObj = $qb->getQuery()->getSingleResult();
			return AccountRepository::get($tokenObj->uid);
		} catch (NoResultException $e) {
			return null;
		} catch (NonUniqueResultException $e) {
			throw $e;
		}
	}

	/**
	 * Is current session verified by an one time token to execute a certain url on behalf of the given user uid?
	 *
	 * @param string $uid
	 * @param string $url
	 * @return boolean
	 */
	public function isVerified($uid, $url) {
		$token = $this->find(['uid' => $uid, 'url' => $url]);
		if ($token) {
			return $token->verified AND LoginService::getUid() === $token->uid AND strtotime($token->expire) > time();
		}
		return false;
	}

	/**
	 * @param string $uid
	 * @param string $url
	 */
	public function discardToken($uid, $url) {
		$this->getEntityManager()->remove($this->getEntityManager()->getReference(OneTimeToken::class, ['uid' => $uid, 'url' => $url]));
		$this->getEntityManager()->flush();
	}

	/**
	 * @param string $uid
	 * @param string $url
	 *
	 * @return array
	 * @throws ORMException
	 * @throws OptimisticLockException
	 */
	public function createToken($uid, $url) {
		$rand = crypto_rand_token(255);
		$token = new OneTimeToken();
		$token->uid = $uid;
		$token->url = $url;
		$token->token = hash('sha512', $rand);
		$token->expire = date_create_immutable(instelling('beveiliging', 'one_time_token_expire_after'));
		$token->verified = false;
		$this->getEntityManager()->persist($token);
		$this->getEntityManager()->flush();

		return array($rand, $token->expire);
	}

	/**
	 */
	public function opschonen() {
		$this->createQueryBuilder('t')
			->delete()
			->where('t.expire <= :now')
			->setParameter('now', date_create_immutable())
			->getQuery()->execute();
	}

}
