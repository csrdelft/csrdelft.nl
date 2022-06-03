<?php

namespace CsrDelft\view\formulier\getalvelden;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\invoervelden\InputField;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Invoeren van een integer. Eventueel met minima/maxima. Leeg evt. toegestaan.
 */
class IntField extends InputField {

	public $type = 'number';
	public $pattern = '[0-9]+';
	public $step = 1;
	public $min = null;
	public $max = null;

	public function __construct($name, $value, $description, $min = null, $max = null) {
		parent::__construct($name, $value, $description, 11);
		if (!is_int($this->value) AND $this->value !== null) {
			throw new CsrGebruikerException('value geen int');
		}
		if (!is_int($this->origvalue) AND $this->origvalue !== null) {
			throw new CsrGebruikerException('origvalue geen int');
		}
		if (is_int($min)) {
			$this->min = $min;
		}
		if (is_int($max)) {
			$this->max = $max;
		}
	}

	public function getValue() {
		if ($this->isPosted()) {
			$this->value = filter_input(INPUT_POST, $this->name, FILTER_SANITIZE_NUMBER_INT);
			if ($this->value !== '') {
				$this->value = (int)$this->value;
			}
		}
		if ($this->empty_null AND $this->value == '' AND $this->value !== 0) {
			$this->value = null;
		}
		return $this->value;
	}

	public function validate() {
		if ($this->value === 0) {
			return true;
		}
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		} elseif (!preg_match('/^' . $this->pattern . '$/', $this->value)) {
			$this->error = 'Alleen gehele getallen toegestaan';
		} elseif (is_int($this->max) AND $this->value > $this->max) {
			$this->error = 'Maximale waarde is ' . $this->max . ' ';
		} elseif ($this->leden_mod AND LoginService::mag(P_LEDEN_MOD)) {
			// exception for leden mod
		} elseif (is_int($this->min) AND $this->value < $this->min) {
			$this->error = 'Minimale waarde is ' . $this->min . ' ';
		}
		return $this->error === '';
	}

	public function getHtml() {
		return ' <input ' . $this->getInputAttribute(array('type', 'id', 'name', 'class', 'value', 'origvalue', 'pattern', 'disabled', 'readonly', 'maxlength', 'placeholder', 'autocomplete', 'min', 'max', 'step')) . ' /> ';
	}
}
