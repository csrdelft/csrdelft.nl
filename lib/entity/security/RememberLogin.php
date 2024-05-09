<?php

namespace CsrDelft\entity\security;

use CsrDelft\repository\security\RememberLoginRepository;
use CsrDelft\common\Util\DateUtil;
use CsrDelft\Component\DataTable\DataTableEntry;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\view\Icon;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentTokenInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * RememberLogin.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
#[
	ORM\Entity(
		repositoryClass: RememberLoginRepository::class
	)
]
#[ORM\Table('login_remember')]
class RememberLogin implements DataTableEntry, PersistentTokenInterface
{
	/**
	 * Primary key
	 * @var int
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'integer')]
	#[ORM\Id]
	#[ORM\GeneratedValue]
	public $id;
	/**
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $series;
	/**
	 * Token string
	 * @var string
	 */
	#[ORM\Column(type: 'string')]
	public $token;
	/**
	 * Lidnummer
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'uid')]
	public $uid;
	/**
	 * @var Profiel
	 */
	#[ORM\ManyToOne(targetEntity: Profiel::class)]
	#[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
	public $profiel;
	/**
	 * DateTime
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'datetime_immutable')]
	public $remember_since;
	/**
	 * @var DateTimeImmutable
	 */
	#[ORM\Column(type: 'datetime_immutable')]
	public $last_used;
	/**
	 * Device name
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'string')]
	public $device_name;
	/**
	 * IP address
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'string')]
	public $ip;
	/**
	 * Sessie koppelen aan ip
	 * @var boolean
	 */
	#[ORM\Column(type: 'boolean')]
	public $lock_ip;

	/**
	 * @return string|null
	 */
	#[Serializer\SerializedName('lock_ip')]
	#[Serializer\Groups('datatable')]
	public function getDataTableLockIp()
	{
		return $this->lock_ip
			? Icon::getTag('lock', null, 'Gekoppeld aan IP-adres')
			: '';
	}

	/**
	 * @return string
	 */
	#[Serializer\SerializedName('remember_since')]
	#[Serializer\Groups('datatable')]
	public function getDataTableRememberSince()
	{
		return DateUtil::reldate($this->remember_since);
	}

	/**
	 * @return string|false
	 */
	#[Serializer\SerializedName('last_used')]
	#[Serializer\Groups('datatable')]
	public function getDataTableLastUsed()
	{
		return DateUtil::reldate($this->last_used);
	}

	public function getClass(): string
	{
		return Account::class;
	}

	public function getUsername(): string
	{
		return $this->uid;
	}

	public function getSeries(): string
	{
		return $this->series;
	}

	public function getTokenValue(): string
	{
		return $this->token;
	}

	public function getLastUsed(): DateTime
	{
		return DateTime::createFromImmutable($this->last_used);
	}

	public function getUserIdentifier(): string
	{
		return $this->uid;
	}
}
