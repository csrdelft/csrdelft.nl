<?php


namespace CsrDelft\entity\security;


use CsrDelft\entity\security\enum\RemoteLoginStatus;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Class RemoteLogin
 * @package CsrDelft\entity\security
 * @ORM\Entity()
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
	 */
	public $expires;
	/**
	 * @var Uuid
	 * @ORM\Column(type="uuid")
	 */
	public $key;
	/**
	 * @var RemoteLoginStatus
	 * @ORM\Column(type="enumRemoteLoginStatus")
	 */
	public $status;

}
