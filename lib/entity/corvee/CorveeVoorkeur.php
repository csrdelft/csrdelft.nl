<?php

namespace CsrDelft\entity\corvee;

use CsrDelft\entity\profiel\Profiel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author P.W.G. Brussee (brussee@live.nl)
 *
 * Een crv_voorkeur instantie beschrijft een voorkeur van een lid om een periodieke taak uit te voeren.
 *
 * @see CorveeRepetitie
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\corvee\CorveeVoorkeurenRepository")
 * @ORM\Table("crv_voorkeuren")
 */
class CorveeVoorkeur
{
	/**
	 * @var string
	 * @ORM\Column(type="uid")
	 * @ORM\Id()
	 */
	public $uid;
	/**
	 * @var integer
	 * @ORM\Column(type="integer")
	 * @ORM\Id()
	 */
	public $crv_repetitie_id;
	/**
	 * @var CorveeRepetitie
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\corvee\CorveeRepetitie")
	 * @ORM\JoinColumn(name="crv_repetitie_id", referencedColumnName="crv_repetitie_id")
	 */
	public $corveeRepetitie;
	/**
	 * @var Profiel
	 * @ORM\ManyToOne(targetEntity="CsrDelft\entity\profiel\Profiel")
	 * @ORM\JoinColumn(name="uid", referencedColumnName="uid")
	 */
	public $profiel;

	public $van_uid;

	public function setProfiel(Profiel $profiel = null): void
	{
		$this->profiel = $profiel;
		$this->uid = $profiel->uid ?? null;
	}

	public function setCorveeRepetitie(CorveeRepetitie $corveeRepetitie = null): void
	{
		$this->corveeRepetitie = $corveeRepetitie;
		$this->crv_repetitie_id = $corveeRepetitie->crv_repetitie_id ?? null;
	}
}
