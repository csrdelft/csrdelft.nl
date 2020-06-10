<?php

namespace CsrDelft\entity\groepen;

use Doctrine\ORM\Mapping as ORM;

/**
 * RechtenGroepLid.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een lid van een rechten-groep.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\leden\RechtenGroepLedenRepository")
 * @ORM\Table("groep_leden", indexes={
 *   @ORM\Index(name="lid_sinds", columns={"lid_sinds"})
 * })
 */
class RechtenGroepLid extends AbstractGroepLid {
	/**
	 * @var RechtenGroep
	 * @ORM\ManyToOne(targetEntity="RechtenGroep", inversedBy="leden")
	 */
	public $groep;

	/**
	 * @inheritDoc
	 */
	public function getGroep() {
		return $this->groep;
	}
}
