<?php

namespace CsrDelft\view\formulier\getalvelden;
/**
 * BedragField.class.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * Invoeren van een bedrag in centen, dus precisie van 2 cijfers achter de komma.
 *
 */
class BedragField extends IntField {

	public $valuta;

	public $pattern = '-?[0-9]+';

	public function __construct($name, $value, $description, $valuta = '€', $min = null, $max = null, $step = 0.01) {
		parent::__construct($name, $value, $description, is_numeric($min) ? intval($min * 100) : null, is_numeric($max) ? intval($max * 100) : null);
		$this->step = $step * 100;
		$this->valuta = $valuta;
	}

	public function getHtml() {
		return $this->valuta . parent::getHtml() . '<span class="lichtgrijs">(in centen)</span>';
	}

}
