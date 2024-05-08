<?php

namespace CsrDelft\entity\security;

use CsrDelft\entity\security\enum\RemoteLoginStatus;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Class RemoteLogin
 * @package CsrDelft\entity\security
 */
#[ORM\Entity(repositoryClass: \CsrDelft\repository\security\RemoteLoginRepository::class)]
class RemoteLogin
{
	/**
  * @var int
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\GeneratedValue]
 #[ORM\Id]
 public $id;
	/**
  * @var \DateTimeImmutable
  * @Serializer\Groups("json")
  */
 #[ORM\Column(type: 'datetime')]
 public $expires;
	/**
  * @var Uuid
  * @Serializer\Groups("json")
  */
 #[ORM\Column(type: 'uuid')]
 public $uuid;
	/**
  * @var RemoteLoginStatus
  * @Serializer\Groups("json")
  */
 #[ORM\Column(type: 'enumRemoteLoginStatus')]
 public $status;
	/**
  * @var Account|null
  */
 #[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: \CsrDelft\entity\security\Account::class)]
 public $account;
}
