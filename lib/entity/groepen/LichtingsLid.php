<?php

namespace CsrDelft\entity\groepen;


use Doctrine\ORM\Mapping as ORM;

/**
 * LichtingsLid.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een lid van een lichting.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\leden\LichtingLedenRepository")
 * @ORM\Table("lichting_leden", indexes={
 *   @ORM\Index(name="lid_sinds", columns={"lid_sinds"})
 * })
 */
class LichtingsLid extends AbstractGroepLid {
	/**
	 * @var Lichting
	 * @ORM\ManyToOne(targetEntity="Lichting", inversedBy="leden")
	 */
	public $groep;

	/**
	 * @inheritDoc
	 */
	public function getGroep() {
		return $this->groep;
	}
}
