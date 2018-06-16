<?php

namespace CsrDelft\view\formulier\getalvelden;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\invoervelden\InputField;

/**
 * IntField.class.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * Invoeren van een integer. Eventueel met minima/maxima. Leeg evt. toegestaan.
 */
class IntField extends InputField {

	public $type = 'number';
	public $pattern = '[0-9]+';
	public $step = 1;
	public $min = null;
	public $max = null;
	public $min_alert = null;
	public $max_alert = null;

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
			$this->min_alert = 'Minimaal ' . $this->min;
		}
		if (is_int($max)) {
			$this->max = $max;
			$this->max_alert = 'Maximaal ' . $this->max;
		}
		$this->onkeydown .= <<<JS

	if (event.keyCode === 107 || event.keyCode === 109) {
		event.preventDefault();
		if (event.keyCode === 107) {
			$('#add_{$this->getId()}').click();
		}
		else if (event.keyCode === 109) {
			$('#substract_{$this->getId()}').click();
		}
		return false;
	}
JS;
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
		} elseif ($this->leden_mod AND LoginModel::mag('P_LEDEN_MOD')) {
			// exception for leden mod
		} elseif (is_int($this->min) AND $this->value < $this->min) {
			$this->error = 'Minimale waarde is ' . $this->min . ' ';
		}
		return $this->error === '';
	}

	public function getHtml() {
		$html = '';

		if ($this->min !== null) {
			if ($this->min_alert) {
				$alert = "alert('{$this->min_alert}');";
			} else {
				$alert = '';
			}
			$this->onchange .= <<<JS

	if (parseInt( $(this).val() ) < $(this).attr('min')) {
		{$alert}
		$(this).val( $(this).attr('min') );
	}
	$('#substract_{$this->getId()}').toggleClass('disabled', parseInt( $(this).val() ) <= $(this).attr('min'));
JS;
		}
		if ($this->max !== null) {
			if ($this->max_alert) {
				$alert = "alert('{$this->max_alert}');";
			} else {
				$alert = '';
			}
			$this->onchange .= <<<JS

	if (parseInt( $(this).val() ) >  $(this).attr('max')) {
		{$alert}
		$(this).val( $(this).attr('max') );
	}
	$('#add_{$this->getId()}').toggleClass('disabled', parseInt( $(this).val() ) >=  $(this).attr('max'));
JS;
		}

		$html .= ' <input ' . $this->getInputAttribute(array('type', 'id', 'name', 'class', 'value', 'origvalue', 'pattern', 'disabled', 'readonly', 'maxlength', 'placeholder', 'autocomplete', 'min', 'max', 'step')) . ' /> ';

		return $html;
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS

$('#add_{$this->getId()}').click(function () {
	var val = parseInt($('#{$this->getId()}').val());
	if ($(this).hasClass('disabled') || isNaN(val)) {
		return;
	}
	$('#{$this->getId()}').val(val + {$this->step}).change();
});
$('#substract_{$this->getId()}').click(function () {
	var val = parseInt($('#{$this->getId()}').val());
	if ($(this).hasClass('disabled') || isNaN(val)) {
		return;
	}
	$('#{$this->getId()}').val(val - {$this->step}).change();
});
JS;
	}

}
