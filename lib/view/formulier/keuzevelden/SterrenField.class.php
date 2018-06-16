<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\view\formulier\getalvelden\FloatField;

/**
 * SterrenField.class.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * Sterren
 */
class SterrenField extends FloatField {

	public $click_submit = false;
	public $reset;
	public $half;
	public $hints;

	public function __construct($name, $value, $description, $max_stars = 5, $half = false, $reset = false) {
		parent::__construct($name, $value, $description, $half ? 1 : 0, 1, $max_stars);
		$this->reset = $reset;
		$this->half = $half;
		$this->hints = array_fill(0, $max_stars, '');
	}

	public function getHtml() {
		return '<div ' . $this->getInputAttribute(array('id', 'name', 'class')) . ' />';
	}

	public function getJavascript() {
		$settings = json_encode(array(
			'scoreName' => $this->name,
			'path' => '/images/raty/',
			'score' => $this->getValue(),
			'number' => $this->max,
			'half' => (boolean)$this->half,
			'hints' => $this->hints,
			'readOnly' => (boolean)$this->readonly,
			'cancel' => (boolean)$this->reset,
			'cancelHint' => 'Wis beoordeling',
			'cancelPlace' => 'right',
			'noRatedMsg' => ''
		));
		$js = parent::getJavascript() . <<<JS

var settings{$this->getId()} = {$settings};
settings{$this->getId()}['click'] = function(score, event) {

JS;
		if ($this->click_submit) {
			$js .= "$(this).raty('score', score);$(this).closest('form').submit();";
		}
		return $js . <<<JS
};
$("#{$this->getId()}").raty(settings{$this->getId()});
JS;
	}

}
