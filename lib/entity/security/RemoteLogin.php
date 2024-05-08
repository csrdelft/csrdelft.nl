<?php

namespace CsrDelft\entity\security;

use CsrDelft\repository\security\RemoteLoginRepository;
use DateTimeImmutable;
use CsrDelft\entity\security\enum\RemoteLoginStatus;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Class RemoteLogin
 * @package CsrDelft\entity\security
 */
#[ORM\Entity(repositoryClass: RemoteLoginRepository::class)]
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
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'datetime')]
 #[Serializer\Groups('json')]
 public $expires;
	/**
  * @var Uuid
  */
 #[ORM\Column(type: 'uuid')]
 #[Serializer\Groups('json')]
 public $uuid;
	/**
  * @var RemoteLoginStatus
  */
 #[ORM\Column(type: 'enumRemoteLoginStatus')]
 #[Serializer\Groups('json')]
 public $status;
	/**
  * @var Account|null
  */
 #[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: Account::class)]
 public $account;
}
