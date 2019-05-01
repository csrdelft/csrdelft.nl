<?php

namespace CsrDelft\model\entity\groepen;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/04/2019
 */
class GroepKeuze {
	public function __wakeup() {
		assert(in_array($this->type, GroepKeuzeType::getTypeOptions()));
	}

  public $naam;
	public $type; // Checks, radios, dropdown, text, slider, number, date
	public $opties; // String, names, name
	public $default; // String, names, name
	public $description;

	public function __construct($naam = null, $type = null, $default = null, $description = null) {
		$this->naam = $naam;
		$this->type = $type;
		// TODO: Niet alleen een bool
		$this->default = (bool)$default;
		$this->description = $description;
	}
}
