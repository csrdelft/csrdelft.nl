<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\view\formulier\getalvelden\FloatField;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Sterren
 */
class SterrenField extends FloatField
{
	public $click_submit = false;
	public $reset;
	public $half;
	public $hints;

	public function __construct(
		$name,
		$value,
		$description,
		$max_stars = 5,
		$half = false,
		$reset = false
	) {
		parent::__construct(
			$name,
			$value,
			$description,
			$half ? 1 : 0,
			1,
			$max_stars
		);
		$this->reset = $reset;
		$this->half = $half;
		$this->hints = array_fill(0, $max_stars, '');
		$this->css_classes[] = 'SterrenField';
	}

	public function getHtml()
	{
		$attributes = $this->getInputAttribute(['id', 'name', 'class']);
		$config = htmlspecialchars(
			json_encode([
				'scoreName' => $this->name,
				'score' => $this->getValue(),
				'number' => $this->max,
				'half' => (bool) $this->half,
				'hints' => $this->hints,
				'readOnly' => (bool) $this->readonly,
				'cancel' => (bool) $this->reset,
			])
		);

		return "<div {$attributes} data-config=\"{$config}\"></div>";
	}
}
