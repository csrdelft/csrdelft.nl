<?php

namespace CsrDelft\entity\groepen;

use Doctrine\ORM\Mapping as ORM;

/**
 * OnderverenigingsLid.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een lid van een ondervereniging.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\leden\OnderverenigingsLedenRepository")
 * @ORM\Table("ondervereniging_leden", indexes={
 *   @ORM\Index(name="lid_sinds", columns={"lid_sinds"})
 * })
 */
class OnderverenigingsLid extends AbstractGroepLid {
	/**
	 * @var Ondervereniging
	 * @ORM\ManyToOne(targetEntity="Ondervereniging", inversedBy="leden")
	 */
	public $groep;

	/**
	 * @inheritDoc
	 */
	public function getGroep() {
		return $this->groep;
	}
}
