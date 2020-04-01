<?php

namespace CsrDelft\entity;

use CsrDelft\entity\profiel\Profiel;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class VoorkeurVoorkeur
 * @package CsrDelft\model\entity
 * @ORM\Entity(repositoryClass="CsrDelft\repository\StekPakketRepository")
 * @ORM\Table("stekpakket")
 */
class StekPakket {
	/**
	 * @var string
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 */
	public $uid;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	public $basispakket;

	/**
	 * @var string
	 * @ORM\Column(type="decimal")
	 */
	public $prijs;

	/**
	 * @var string[]
	 * @ORM\Column(type="simple_array")
	 */
	public $opties;

	/**
	 * @var \DateTimeImmutable
	 * @ORM\Column(type="datetime")
	 */
	public $timestamp;

	public function setTimestamp() {
		$this->timestamp = new \DateTimeImmutable();
	}

	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
	 */
	public $profiel;

	/**
	 * uid is onderdeel van primary key en moet dus gezet zijn bij saven.
	 *
	 * @param Profiel $profiel
	 */
	public function setProfiel(Profiel $profiel) {
		$this->profiel = $profiel;
		$this->uid = $profiel->uid;
	}
}
