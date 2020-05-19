<?php

namespace CsrDelft\entity\security;

use CsrDelft\common\datatable\DataTableEntry;
use CsrDelft\view\Icon;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * RememberLogin.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\security\RememberLoginRepository")
 * @ORM\Table("login_remember")
 */
class RememberLogin implements DataTableEntry {
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
	 * DateTime
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $remember_since;
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
	public function getDataTableLockIp() {
		return $this->lock_ip ? Icon::getTag('lock', null, 'Gekoppeld aan IP-adres') : '';
	}

	/**
	 * @return string
	 * @Serializer\SerializedName("remember_since")
	 * @Serializer\Groups("datatable")
	 */
	public function getDataTableRememberSince() {
		return reldate($this->remember_since);
	}
}
