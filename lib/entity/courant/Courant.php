<?php

namespace CsrDelft\entity\courant;

use CsrDelft\repository\CourantRepository;
use CsrDelft\entity\profiel\Profiel;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Courant
 * @package CsrDelft\entity\courant
 */
#[ORM\Table('courant')]
#[ORM\Entity(repositoryClass: CourantRepository::class)]
class Courant
{
	/**
  * @var integer
  */
 #[ORM\Column(type: 'integer')]
 #[ORM\Id]
 #[ORM\GeneratedValue]
 public $id;
	/**
  * @var DateTimeImmutable
  */
 #[ORM\Column(type: 'datetime', name: 'verzendMoment')]
 public $verzendMoment;
	/**
  * @var string
  */
 #[ORM\Column(type: 'text')]
 public $inhoud;
	/**
  * @var string
  */
 #[ORM\Column(type: 'uid')]
 public $verzender;
	/**
  * @var Profiel
  */
 #[ORM\JoinColumn(name: 'verzender', referencedColumnName: 'uid')]
 #[ORM\ManyToOne(targetEntity: Profiel::class)]
 public $verzender_profiel;

	public function getJaar()
	{
		return $this->verzendMoment->format('Y');
	}
}
