<?php

namespace CsrDelft\entity\security;

use CsrDelft\repository\security\AccessRepository;
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
#[ORM\Entity(repositoryClass: AccessRepository::class)]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
class AccessControl
{
	/**
  * AclController / View / etc.
  * @var string
  */
 #[ORM\Column(type: 'stringkey')]
 #[ORM\Id]
 #[Serializer\Groups('datatable')]
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
 #[ORM\Column(type: 'string')]
 #[Serializer\Groups('datatable')]
 public $subject;

	/**
  * @return string
  */
 #[Serializer\Groups('datatable')]
 #[Serializer\SerializedName('action')]
 public function getDataTableAction()
	{
		return AccessAction::from($this->action)->getDescription();
	}

	/**
  * @return string
  */
 #[Serializer\Groups('datatable')]
 #[Serializer\SerializedName('resource')]
 public function getDataTableResource(): string
	{
		if ($this->resource === '*') {
			return 'Elke ' . lcfirst($this->environment);
		} else {
			return 'Deze ' . lcfirst($this->environment);
		}
	}
}
