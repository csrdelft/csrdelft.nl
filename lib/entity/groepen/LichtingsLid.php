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
 */
class LichtingsLid extends AbstractGroepLid {

	protected static $table_name = 'lichting_leden';

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
