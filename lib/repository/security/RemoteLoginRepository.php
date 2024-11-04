<?php

namespace CsrDelft\repository\security;

use CsrDelft\entity\security\enum\RemoteLoginStatus;
use CsrDelft\entity\security\RemoteLogin;
use CsrDelft\repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class RemoteLoginRepository extends AbstractRepository
{


	public function refresh(object $remoteLogin)
	{
		$remoteLogin->status = RemoteLoginStatus::PENDING();
		$remoteLogin->expires = date_create_immutable('+1 minute');
		$remoteLogin->uuid = Uuid::v4();
	}

	public function nieuw()
	{
		$remoteLogin = new RemoteLogin();

		$this->refresh($remoteLogin);

		return $remoteLogin;
	}
}
