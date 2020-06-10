<?php

namespace CsrDelft\entity\groepen;


use Doctrine\ORM\Mapping as ORM;

/**
 * ActiviteitDeelnemer.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een deelnemer van een activiteit.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\leden\ActiviteitDeelnemersRepository")
 * @ORM\Table("activiteit_deelnemers", indexes={
 *   @ORM\Index(name="lid_sinds", columns={"lid_sinds"})
 * })
 */
class ActiviteitDeelnemer extends AbstractGroepLid {
	/**
	 * @var Activiteit
	 * @ORM\ManyToOne(targetEntity="Activiteit", inversedBy="leden")
	 */
	public $groep;

	public function getGroep() {
		return $this->groep;
	}
}
