<?php

namespace CsrDelft\repository\security;

use CsrDelft\entity\security\Account;
use CsrDelft\entity\security\RememberOAuth;
use CsrDelft\repository\AbstractRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * @method RememberOAuth|null find($id, $lockMode = null, $lockVersion = null)
 * @method RememberOAuth|null findOneBy(array $criteria, array $orderBy = null)
 * @method RememberOAuth[]    findAll()
 * @method RememberOAuth[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method RememberOAuth|null retrieveByUuid($UUID)
 */
class RememberOAuthRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, RememberOAuth::class);
	}

	/**
	 * @return RememberOAuth
	 */
	public function nieuw(
		UserInterface $account,
		$clientIdentifier,
		$scopes
	): RememberOAuth {
		$remember = new RememberOAuth();
		$remember->account = $account;
		$remember->uid = $account->uid;
		$remember->rememberSince = date_create_immutable();
		$remember->lastUsed = date_create_immutable();
		$remember->clientIdentifier = $clientIdentifier;
		$remember->scopes = implode(' ', $scopes);

		$this->_em->persist($remember);
		$this->_em->flush();

		return $remember;
	}

	public function findByUser(string $userIdentifier, string $clientIdentifier)
	{
		return $this->findOneBy([
			'clientIdentifier' => $clientIdentifier,
			'uid' => $userIdentifier,
		]);
	}
}
