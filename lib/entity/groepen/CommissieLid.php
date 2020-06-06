<?php

namespace CsrDelft\entity\groepen;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommissieLid.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een lid van een commissie.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\leden\CommissieLedenRepository")
 * @ORM\Table("commissie_leden", indexes={
 *   @ORM\Index(name="lid_sinds", columns={"lid_sinds"})
 * })
 */
class CommissieLid extends AbstractGroepLid {
	/**
	 * @var Commissie
	 * @ORM\ManyToOne(targetEntity="Commissie", inversedBy="leden")
	 */
	public $groep;

	/**
	 * @inheritDoc
	 */
	public function getGroep() {
		return $this->groep;
	}
}
