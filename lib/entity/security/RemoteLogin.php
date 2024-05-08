<?php

namespace CsrDelft\entity\security;

use CsrDelft\entity\security\enum\RemoteLoginStatus;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Class RemoteLogin
 * @package CsrDelft\entity\security
 * @ORM\Entity(repositoryClass="CsrDelft\repository\security\RemoteLoginRepository")
 */
class RemoteLogin
{
	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue()
	 * @ORM\Id()
	 */
	public $id;
	/**
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 * @Serializer\Groups("json")
	 */
	public $expires;
	/**
	 * @var Uuid
	 * @ORM\Column(type="uuid")
	 * @Serializer\Groups("json")
	 */
	public $uuid;
	/**
	 * @var RemoteLoginStatus
	 * @ORM\Column(type="enumRemoteLoginStatus")
	 * @Serializer\Groups("json")
	 */
	public $status;
	/**
	 * @var Account|null
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\security\Account")
	 * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
	 */
	public $account;
}
