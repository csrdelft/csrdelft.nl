<?php
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

	public function __construct($name, $value, $description, $valuta = '€', $min = null, $max = null, $step = 0.01) {
		parent::__construct($name, $value, $description, $min * 100, $max * 100);
		$this->step = $step * 100;
		$this->valuta = $valuta;
	}

	public function getHtml() {
		return $this->valuta . parent::getHtml(false) . '<span class="lichtgrijs">(in centen)</span>';
	}

}