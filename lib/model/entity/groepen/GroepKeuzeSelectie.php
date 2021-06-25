<?php

namespace CsrDelft\model\entity\groepen;

use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/04/2019
 */
class GroepKeuzeSelectie {
	/**
	 * @var string|null
	 * @Serializer\Groups("vue")
	 */
	public $naam;
	/**
	 * @var string|null
	 * @Serializer\Groups("vue")
	 */
	public $selectie;

	public function __construct($naam = null, $selectie = null) {
		$this->naam = $naam;
		$this->selectie = $selectie;
	}
}
