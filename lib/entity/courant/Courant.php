<?php

namespace CsrDelft\entity\courant;

use CsrDelft\entity\profiel\Profiel;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Courant
 * @package CsrDelft\entity\courant
 * @ORM\Entity(repositoryClass="CsrDelft\repository\CourantRepository")
 * @ORM\Table("courant")
 */
class Courant
{
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 */
	public $id;
	/**
	 * @var DateTimeImmutable
	 * @ORM\Column(type="datetime", name="verzendMoment")
	 */
	public $verzendMoment;
	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	public $inhoud;
	/**
	 * @var string
	 * @ORM\Column(type="uid")
	 */
	public $verzender;
	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="verzender", referencedColumnName="uid")
	 */
	public $verzender_profiel;

	public function getJaar(): string
	{
		return $this->verzendMoment->format('Y');
	}
}
