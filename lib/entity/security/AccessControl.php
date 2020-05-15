<?php

namespace CsrDelft\entity\security;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccessControl.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * ACL-entry.
 * @ORM\Entity(repositoryClass="CsrDelft\repository\security\AccessRepository")
 * @ORM\Table("acl")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE")
 */
class AccessControl {
	/**
	 * AclController / PersistentEntity / View / etc.
	 * @var string
	 * @ORM\Column(type="stringkey")
	 * @ORM\Id()
	 */
	public $environment;
	/**
	 * Action
	 * @var string
	 * @ORM\Column(type="stringkey")
	 * @ORM\Id()
	 */
	public $action;
	/**
	 * UUID
	 * @var string
	 * @ORM\Column(type="stringkey")
	 * @ORM\Id()
	 */
	public $resource;
	/**
	 * Benodigde rechten
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $subject;
}
