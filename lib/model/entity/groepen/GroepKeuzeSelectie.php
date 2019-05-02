<?php

namespace CsrDelft\model\entity\groepen;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/04/2019
 */
class GroepKeuzeSelectie {
	public $naam;
	public $selectie;

	public function __construct($naam = null, $selectie = null) {
		$this->naam = $naam;
		$this->selectie = $selectie;
	}
}
