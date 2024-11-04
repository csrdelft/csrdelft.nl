<?php

namespace CsrDelft\entity\security;

use CsrDelft\entity\security\enum\AccessAction;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * AccessControl.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * ACL-entry.
 */
#[
	ORM\Entity(
		repositoryClass: \CsrDelft\repository\security\AccessRepository::class
	)
]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
#[ORM\Table('acl')]
class AccessControl
{
	/**
	 * AclController / View / etc.
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'stringkey')]
	#[ORM\Id]
	public $environment;
	/**
	 * Action
	 * @var string
	 */
	#[ORM\Column(type: 'stringkey')]
	#[ORM\Id]
	public $action;
	/**
	 * UUID
	 * @var string
	 */
	#[ORM\Column(type: 'stringkey')]
	#[ORM\Id]
	public $resource;
	/**
	 * Benodigde rechten
	 * @var string
	 */
	#[Serializer\Groups('datatable')]
	#[ORM\Column(type: 'string')]
	public $subject;
}
