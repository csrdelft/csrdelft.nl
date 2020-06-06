<?php

namespace CsrDelft\entity\groepen;

use Doctrine\ORM\Mapping as ORM;

/**
 * KetzerDeelnemer.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Een deelnemer van een ketzer.
 *
 * @ORM\Entity(repositoryClass="CsrDelft\repository\groepen\leden\KetzerDeelnemersRepository")
 * @ORM\Table("ketzer_deelnemers", indexes={
 *   @ORM\Index(name="lid_sinds", columns={"lid_sinds"})
 * })
 */
class KetzerDeelnemer extends AbstractGroepLid {
	/**
	 * @var Ketzer
	 * @ORM\ManyToOne(targetEntity="Ketzer", inversedBy="leden")
	 */
	public $groep;

	/**
	 * @inheritDoc
	 */
	public function getGroep() {
		return $this->groep;
	}
}
