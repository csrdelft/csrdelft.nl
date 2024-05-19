<?php

namespace CsrDelft\repository\security;

use CsrDelft\entity\security\enum\RemoteLoginStatus;
use CsrDelft\entity\security\RemoteLogin;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class RemoteLoginRepository extends AbstractRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, RemoteLogin::class);
	}

	public function refresh($remoteLogin)
	{
		$remoteLogin->status = RemoteLoginStatus::PENDING();
		$remoteLogin->expires = date_create_immutable('+1 minute');
		$remoteLogin->uuid = Uuid::v4();
	}

	public function nieuw(): RemoteLogin
	{
		$remoteLogin = new RemoteLogin();

		$this->refresh($remoteLogin);

		return $remoteLogin;
	}

	/**
	 * Gooi alle oude sessies weg.
	 */
	public function opschonen()
	{
		$this->createQueryBuilder('rl')
			->delete()
			->where('rl.expires > :now')
			->setParameter('now', date_create_immutable('+1 hour'))
			->getQuery()
			->execute();
	}
}
