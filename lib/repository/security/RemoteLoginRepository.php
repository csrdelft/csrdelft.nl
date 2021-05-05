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

	public function nieuw() {
		$remoteLogin = new RemoteLogin();

		$remoteLogin->status = RemoteLoginStatus::PENDING();
		$remoteLogin->expires = date_create_immutable()->add(new \DateInterval('PT1M'));
		$remoteLogin->uuid = Uuid::v4();

		return $remoteLogin;
	}

}
