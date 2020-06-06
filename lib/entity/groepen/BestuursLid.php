<?php

namespace CsrDelft\entity\groepen;

use Doctrine\ORM\Mapping as ORM;

/**
 * BestuursLid.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een lid van een bestuur.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\leden\BestuursLedenRepository")
 * @ORM\Table("bestuurs_leden", indexes={
 *   @ORM\Index(name="lid_sinds", columns={"lid_sinds"})
 * })
 */
class BestuursLid extends AbstractGroepLid {
	/**
	 * @var Bestuur
	 * @ORM\ManyToOne(targetEntity="Bestuur", inversedBy="leden")
	 */
	public $groep;

	/**
	 * @inheritDoc
	 */
	public function getGroep() {
		return $this->groep;
	}
}
