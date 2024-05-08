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
#[ORM\Table('acl')]
#[ORM\Entity(repositoryClass: \CsrDelft\repository\security\AccessRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
class AccessControl
{
	/**
  * AclController / View / etc.
  * @var string
  * @Serializer\Groups("datatable")
  */
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
  * @Serializer\Groups("datatable")
  */
 #[ORM\Column(type: 'string')]
 public $subject;

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("action")
	 */
	public function getDataTableAction()
	{
		return AccessAction::from($this->action)->getDescription();
	}

	/**
	 * @return string
	 * @Serializer\Groups("datatable")
	 * @Serializer\SerializedName("resource")
	 */
	public function getDataTableResource()
	{
		if ($this->resource === '*') {
			return 'Elke ' . lcfirst($this->environment);
		} else {
			return 'Deze ' . lcfirst($this->environment);
		}
	}
}
