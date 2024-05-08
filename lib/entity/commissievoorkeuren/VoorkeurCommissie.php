<?php

namespace CsrDelft\entity\commissievoorkeuren;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class VoorkeurCommissie
 * @package CsrDelft\entity\commissievoorkeuren
 */
#[ORM\Entity(repositoryClass: \CsrDelft\repository\commissievoorkeuren\VoorkeurCommissieRepository::class)]
class VoorkeurCommissie
{
	/**
  * @var int
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 #[ORM\GeneratedValue]
 public $id;

	/**
  * @var string
  */
 #[ORM\Column(type: 'string')]
 public $naam;

	/**
  * @var boolean
  */
 #[ORM\Column(type: 'boolean')]
 public $zichtbaar = false;

	/**
  * @var integer
  */
 #[ORM\Column(type: 'integer', options: ['default' => 1])]
 public $categorie_id;

	/**
  * @var VoorkeurCommissieCategorie
  */
 #[ORM\ManyToOne(targetEntity: \VoorkeurCommissieCategorie::class, inversedBy: 'commissies')]
 public $categorie;
}
