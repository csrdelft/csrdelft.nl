<?php

namespace CsrDelft\entity\commissievoorkeuren;

use CsrDelft\repository\commissievoorkeuren\VoorkeurOpmerkingRepository;
use CsrDelft\entity\profiel\Profiel;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class VoorkeurOpmerking
 * @package CsrDelft\entity\commissievoorkeuren
 */
#[ORM\Entity(repositoryClass: VoorkeurOpmerkingRepository::class)]
class VoorkeurOpmerking
{
	/**
  * @var string
  */
 #[ORM\Column(type: 'uid')]
 #[ORM\Id]
 public $uid;

	/**
  * @var string
  */
 #[ORM\Column(type: 'text', nullable: true, name: 'lidOpmerking')]
 public $lidOpmerking;

	/**
  * @var string
  */
 #[ORM\Column(type: 'text', nullable: true, name: 'praesesOpmerking')]
 public $praesesOpmerking;

	/**
  * @var Profiel
  */
 #[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: Profiel::class)]
 public $profiel;
}
