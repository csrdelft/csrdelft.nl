<?php

/**
 * GetalVelden.class.php
 * 
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * 
 * Bevat de uitbreidingen van TextField:
 * 
 * 	- IntField					Integers 
 * 		* DecimalField				Kommagetallen
 * 			- BedragField			Bedragen met 2 cijfers achter de komma
 * 	- TelefoonField				Telefoonnummers
 */

/**
 * Invoeren van een integer. Eventueel met minima/maxima. Leeg evt. toegestaan.
 */
class IntField extends TextField {

	public $type = 'number';
	public $step = 1;
	public $min = null;
	public $max = null;
	public $min_alert = null;
	public $max_alert = null;
	public $valuta = false;

	public function __construct($name, $value, $description, $min = null, $max = null) {
		parent::__construct($name, $value, $description, 11);
		if ($min !== null) {
			$this->min = (int) $min;
			$this->min_alert = 'Minimaal ' . $this->min;
		}
		if ($max !== null) {
			$this->max = (int) $max;
			$this->max_alert = 'Maximaal ' . $this->max;
		}
		$this->onkeydown .= <<<JS

	if (event.keyCode == 107 || event.keyCode == 109) {
		event.preventDefault();
		if (event.keyCode == 107) {
			$('#add_{$this->getId()}').click();
		}
		else if (event.keyCode == 109) {
			$('#substract_{$this->getId()}').click();
		}
		return false;
	}
JS;
	}

	public function getValue() {
		$value = parent::getValue();
		if ($value == '') {
			return null;
		}
		return (int) $value;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		} elseif (!($this instanceof DecimalField) AND ! preg_match('/\d+/', $this->value)) {
			$this->error = 'Alleen gehele getallen toegestaan';
		} elseif ($this->max !== null AND $this->value > $this->max) {
			$this->error = 'Maximale waarde is ' . $this->max . ' ';
		} elseif ($this->leden_mod AND LoginModel::mag('P_LEDEN_MOD')) {
			// exception for leden mod
		} elseif ($this->min !== null AND $this->value < $this->min) {
			$this->error = 'Minimale waarde is ' . $this->min . ' ';
		}
		return $this->error === '';
	}

	public function getHtml() {
		$html = '';
		if (!$this->readonly AND ! $this->disabled AND ! $this->hidden) {
			if ($this->min !== null AND $this->getValue() === $this->min) {
				$class = 'class="disabled"';
			} else {
				$class = '';
			}
			$minus = CSR_PICS . '/knopjes/min.png';
			$html .= <<<HTML
<span id="substract_{$this->getId()}" {$class} style="cursor:pointer;padding:7px;"><img src="{$minus}" alt="-" class="icon" width="20" height="20" /></span>
HTML;
		}

		if ($this->min !== null) {
			if ($this->min_alert) {
				$alert = "alert('{$this->min_alert}');";
			} else {
				$alert = '';
			}
			$this->onchange .= <<<JS

	if (parseFloat( $(this).val() ) < $(this).attr('min')) {
		{$alert}
		$(this).val( $(this).attr('min') );
	}
	$('#substract_{$this->getId()}').toggleClass('disabled', parseFloat( $(this).val() ) <= $(this).attr('min'));
JS;
		}
		if ($this->max !== null) {
			if ($this->max_alert) {
				$alert = "alert('{$this->max_alert}');";
			} else {
				$alert = '';
			}
			$this->onchange .= <<<JS

	if (parseFloat( $(this).val() ) >  $(this).attr('max')) {
		{$alert}
		$(this).val( $(this).attr('max') );
	}
	$('#add_{$this->getId()}').toggleClass('disabled', parseFloat( $(this).val() ) >=  $(this).attr('max'));
JS;
		}

		$html .= $this->valuta . ' <input ' . $this->getInputAttribute(array('type', 'id', 'name', 'class', 'value', 'origvalue', 'disabled', 'readonly', 'maxlength', 'placeholder', 'autocomplete', 'min', 'max', 'step', 'pattern')) . ' /> ';

		if (!$this->readonly AND ! $this->disabled AND ! $this->hidden) {
			if ($this->max !== null AND $this->getValue() === $this->max) {
				$class = 'class="disabled"';
			} else {
				$class = '';
			}
			$plus = CSR_PICS . '/knopjes/plus.png';
			$html .= <<<HTML
<span id="add_{$this->getId()}" {$class} style="cursor:pointer;padding:7px;"><img src="{$plus}" alt="+" class="icon" width="20" height="20" /></span>
HTML;
		}
		return $html;
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS

$('#add_{$this->getId()}').click(function () {
	var val = parseFloat($('#{$this->getId()}').val());
	if ($(this).hasClass('disabled') || isNaN(val)) {
		return;
	}
	$('#{$this->getId()}').val(val + {$this->step}).change();
});
$('#substract_{$this->getId()}').click(function () {
	var val = parseFloat($('#{$this->getId()}').val());
	if ($(this).hasClass('disabled') || isNaN(val)) {
		return;
	}
	$('#{$this->getId()}').val(val - {$this->step}).change();
});
JS;
	}

}

class RequiredIntField extends IntField {

	public $required = true;

}

/**
 * Invoeren van een decimaal getal. Eventueel met minima/maxima. Leeg evt. toegestaan.
 */
class DecimalField extends IntField {

	public $precision;

	public function __construct($name, $value, $description, $precision, $min = null, $max = null, $step = null) {
		parent::__construct($name, $value, $description, $min, $max);
		$this->precision = (int) $precision;
		if (is_float($step)) {
			$this->step = $step;
		} else {
			$this->step = 1.0 / (float) pow(10, $this->precision);
		}
		$this->step = str_replace(',', '.', $this->step); // werkomheen
	}

	public function getValue() {
		$value = parent::getValue();
		if ($value == '') {
			return null;
		}
		return (float) $value;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		} elseif (!preg_match('/^(\d+.\d{' . $this->precision . '})$/', $this->getValue())) {
			$this->error = 'Voer precies ' . $this->precision . ' decimalen in';
		}
		return $this->error === '';
	}

}

class RequiredDecimalField extends DecimalField {

	public $required = true;

}

/**
 * Invoeren van een bedrag in centen, dus precisie van 2 cijfers achter de komma.
 * 
 */
class BedragField extends DecimalField {

	public function __construct($name, $value, $description, $valuta = '€', $min = null, $max = null, $step = null) {
		parent::__construct($name, ((float) $value) / 100.0, $description, 2, $min, $max, $step);
		$this->valuta = $valuta;
	}

	public function getValue() {
		$value = parent::getValue();
		if ($this->empty_null AND empty($value)) {
			return null;
		}
		return (int) ($value * 100.0);
	}

}

class RequiredBedragField extends DecimalField {

	public $required = true;

}

/**
 * TelefoonField
 *
 * is valid als er een enigszins op een telefoonnummer lijkende string wordt
 * ingegeven.
 */
class TelefoonField extends TextField {

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		if (!preg_match('/^([\d\+\-]{10,20})$/', $this->value)) {
			$this->error = 'Geen geldig telefoonnummer.';
		}
		return $this->error === '';
	}

}

class RequiredTelefoonField extends TelefoonField {

	public $required = true;

}
