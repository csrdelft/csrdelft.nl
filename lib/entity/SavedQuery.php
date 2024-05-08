<?php

namespace CsrDelft\entity;

use CsrDelft\service\security\LoginService;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table('savedquery')]
#[ORM\Entity(repositoryClass: \CsrDelft\repository\SavedQueryRepository::class)]
class SavedQuery
{
	/**
  * @var integer
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 #[ORM\GeneratedValue]
 public $ID;
	/**
  * @var string
  */
 #[ORM\Column(type: 'text')]
 public $savedquery;
	/**
  * @var string
  */
 #[ORM\Column(type: 'string')]
 public $beschrijving;
	/**
  * @var string
  */
 #[ORM\Column(type: 'string', options: ['default' => 'P_LOGGED_IN'])]
 public $permissie;
	/**
  * @var string
  */
 #[ORM\Column(type: 'string', options: ['default' => 'Overig'])]
 public $categorie;

	public function magBekijken(): bool
	{
		return LoginService::mag($this->permissie) || LoginService::mag(P_ADMIN);
	}
}
