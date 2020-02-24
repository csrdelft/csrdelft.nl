<?php

namespace CsrDelft\entity\security;

use CsrDelft\Orm\Entity\T;
use Doctrine\ORM\Mapping as ORM;

/**
 * RememberLogin.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\security\RememberLoginRepository")
 * @ORM\Table("login_remember")
 */
class RememberLogin {
	/**
	 * Primary key
	 * @var int
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
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
	 * @ORM\Column(type="string", length=4)
	 */
	public $uid;
	/**
	 * DateTime
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	public $remember_since;
	/**
	 * Device name
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $device_name;
	/**
	 * IP address
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $ip;
	/**
	 * Sessie koppelen aan ip
	 * @var boolean
	 * @ORM\Column(type="boolean")
	 */
	public $lock_ip;
}
