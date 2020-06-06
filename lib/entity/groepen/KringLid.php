<?php

namespace CsrDelft\entity\groepen;

use Doctrine\ORM\Mapping as ORM;

/**
 * KringLid.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een lid van een kring.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\leden\KringLedenRepository")
 * @ORM\Table("kring_leden", indexes={
 *   @ORM\Index(name="lid_sinds", columns={"lid_sinds"})
 * })
 */
class KringLid extends AbstractGroepLid {
	/**
	 * @var Kring
	 * @ORM\ManyToOne(targetEntity="Kring", inversedBy="leden")
	 */
	public $groep;

	public function getGroep() {
		return $this->groep;
	}
}
