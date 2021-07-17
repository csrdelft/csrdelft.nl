<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\view\formulier\invoervelden\TextField;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Date time picker with range (optional).
 */
class DateTimeField extends TextField {

	public $from_datetime;
	public $to_datetime;
	protected $max_jaar;
	protected $min_jaar;

	public function __construct($name, $value, $description, $maxyear = null, $minyear = null) {
		parent::__construct($name, $value, $description);
		if (is_int($maxyear)) {
			$this->max_jaar = $maxyear;
		} else {
			$this->max_jaar = (int)date('Y') + 10;
		}
		if (is_int($minyear)) {
			$this->min_jaar = $minyear;
		} else {
			$this->min_jaar = (int)date('Y') - 10;
		}
		$jaar = (int)date('Y', strtotime($value));
		if ($jaar > $this->max_jaar) {
			$this->max_jaar = $jaar;
		}
		if ($jaar < $this->min_jaar) {
			$this->min_jaar = $jaar;
		}

		$this->cssClasses[] = 'DateTimeField';
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		$jaar = (int)substr($this->value, 0, 4);
		$maand = (int)substr($this->value, 5, 2);
		$dag = (int)substr($this->value, 8, 2);
		$uur = (int)substr($this->value, 11, 2);
		$min = (int)substr($this->value, 14, 2);
		$sec = (int)substr($this->value, 17, 2);
		if (!checkdate($maand, $dag, $jaar)) {
			$this->error = 'Ongeldige datum';
		} elseif ($uur < 0 || $uur > 23 || $min < 0 || $min > 59 || $sec < 0 || $sec > 59) {
			$this->error = 'Ongeldig tijdstip';
		} elseif (is_int($this->max_jaar) && $jaar > $this->max_jaar) {
			$this->error = 'Kies een jaar voor ' . $this->max_jaar;
		} elseif (is_int($this->min_jaar) && $jaar < $this->min_jaar) {
			$this->error = 'Kies een jaar na ' . $this->min_jaar;
		}
		return $this->error === '';
	}

	public function getHtml() {
		$attributes = $this->getInputAttribute(array('type', 'id', 'name', 'class', 'value', 'origvalue', 'disabled', 'readonly', 'maxlength', 'placeholder', 'autocomplete'));

		$minValue = $maxValue = null;

		if ($this->min_jaar) {
			$minValue = $this->min_jaar . '-01-01 00:00';
		}

		if ($this->max_jaar) {
			$maxValue = ($this->max_jaar + 1) . '-01-01 00:00';
		}

		$before = $after = null;

		if ($this->from_datetime) {
			$after = $this->from_datetime->getId();
		}

		if ($this->to_datetime) {
			$before = $this->to_datetime->getId();
		}

		return <<<HTML
<input
 {$attributes}
 data-min-date="{$minValue}"
 data-max-date="{$maxValue}"
 data-after="{$after}"
 data-before="{$before}"
 data-readonly="{$this->readonly}"
/>
HTML;
	}
}
