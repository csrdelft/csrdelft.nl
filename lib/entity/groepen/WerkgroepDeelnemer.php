<?php

namespace CsrDelft\entity\groepen;


use Doctrine\ORM\Mapping as ORM;

/**
 * WerkgroepDeelnemer.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een deelnemer van een werkgroep.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\leden\WerkgroepDeelnemersRepository")
 * @ORM\Table("werkgroep_deelnemers", indexes={
 *   @ORM\Index(name="lid_sinds", columns={"lid_sinds"})
 * })
 */
class WerkgroepDeelnemer extends AbstractGroepLid {
	/**
	 * @var Werkgroep
	 * @ORM\ManyToOne(targetEntity="Werkgroep", inversedBy="leden")
	 */
	public $groep;

	/**
	 * @inheritDoc
	 */
	public function getGroep() {
		return $this->groep;
	}
}
