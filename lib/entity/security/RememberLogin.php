<?php

namespace CsrDelft\entity\security;

use CsrDelft\Component\DataTable\DataTableEntry;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\view\Icon;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Authentication\RememberMe\PersistentTokenInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * RememberLogin.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\security\RememberLoginRepository")
 * @ORM\Table("login_remember")
 */
class RememberLogin implements DataTableEntry, PersistentTokenInterface
{
	/**
	 * Primary key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @Serializer\Groups("datatable")
	 */
	public $id;
	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $series;
	/**
	 * Token string
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $token;
	/**
	 * Lidnummer
	 * @var string
	 * @ORM\Column(type="uid")
	 * @Serializer\Groups("datatable")
	 */
	public $uid;
	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
	 */
	public $profiel;
	/**
	 * DateTime
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $remember_since;
	/**
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $last_used;
	/**
	 * Device name
	 * @var string
	 * @ORM\Column(type="string")
	 * @Serializer\Groups("datatable")
	 */
	public $device_name;
	/**
	 * IP address
	 * @var string
	 * @ORM\Column(type="string")
	 * @Serializer\Groups("datatable")
	 */
	public $ip;
	/**
	 * Sessie koppelen aan ip
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	public $lock_ip;

	/**
	 * @return string|null
	 * @Serializer\SerializedName("lock_ip")
	 * @Serializer\Groups("datatable")
	 */
	public function getDataTableLockIp()
	{
		return $this->lock_ip
			? Icon::getTag('lock', null, 'Gekoppeld aan IP-adres')
			: '';
	}

	/**
	 * @return string
	 * @Serializer\SerializedName("remember_since")
	 * @Serializer\Groups("datatable")
	 */
	public function getDataTableRememberSince()
	{
		return reldate($this->remember_since);
	}

	/**
	 * @return string
	 * @Serializer\SerializedName("last_used")
	 * @Serializer\Groups("datatable")
	 */
	public function getDataTableLastUsed()
	{
		return reldate($this->last_used);
	}

	public function getClass()
	{
		return Account::class;
	}

	public function getUsername()
	{
		return $this->uid;
	}

	public function getSeries()
	{
		return $this->series;
	}

	public function getTokenValue()
	{
		return $this->token;
	}

	public function getLastUsed()
	{
		return $this->last_used;
	}

	public function getUserIdentifier(): string
	{
		return $this->uid;
	}
}
