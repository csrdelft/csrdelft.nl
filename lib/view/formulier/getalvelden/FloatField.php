<?php

namespace CsrDelft\view\formulier\getalvelden;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\view\formulier\invoervelden\InputField;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Invoeren van een decimaal getal. Eventueel met minima/maxima. Leeg evt. toegestaan.
 */
class FloatField extends InputField
{
	public $pattern = null; // html5 input validation pattern

	public $precision;
	public $min = null;
	public $max = null;

	public function __construct(
		$name,
		$value,
		$description,
		$precision,
		$min = null,
		$max = null,
		$step = null
	) {
		parent::__construct($name, $value, $description, $min, $max);
		if (!is_float($this->value) and $this->value !== null) {
			throw new CsrGebruikerException('value geen float');
		}
		if (!is_float($this->origvalue) and $this->origvalue !== null) {
			throw new CsrGebruikerException('origvalue geen float');
		}
		if (is_int($precision)) {
			$this->precision = $precision;
			$this->pattern = '[0-9]*([\.|,][0-9]{' . $this->precision . '})?';
		} else {
			$this->pattern = '[0-9]*([\.|,][0-9]*)?';
		}
		if ($min !== null) {
			$this->min = (float) $min;
		}
		if ($max !== null) {
			$this->max = (float) $max;
		}
		if (is_float($step)) {
			$this->step = $step;
		} else {
			$this->step = 1.0 / (float) pow(10, $this->precision);
		}
		$this->step = str_replace(',', '.', $this->step); // werkomheen
	}

	protected function getInputAttribute($attribute): string
	{
		if ($attribute == 'pattern' && $this->pattern) {
			return 'pattern="' . $this->pattern . '"';
		}
		return parent::getInputAttribute($attribute); // TODO: Change the autogenerated stub
	}

	public function getValue()
	{
		if ($this->isPosted()) {
			$this->value = filter_input(
				INPUT_POST,
				$this->name,
				FILTER_SANITIZE_NUMBER_FLOAT
			);
			if ($this->value !== '') {
				$this->value = (float) $this->value;
			}
		}
		if ($this->empty_null and $this->value == '' and $this->value !== 0.) {
			$this->value = null;
		}
		return $this->value;
	}

	public function validate(): bool
	{
		if ($this->value === 0.) {
			return true;
		}
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		} elseif (!preg_match('/^' . $this->pattern . '$/', $this->getValue())) {
			$this->error = 'Voer maximaal ' . $this->precision . ' decimalen in';
		} elseif ($this->max !== null and $this->value > $this->max) {
			$this->error = 'Maximale waarde is ' . $this->max . ' ';
		} elseif ($this->min !== null and $this->value < $this->min) {
			$this->error = 'Minimale waarde is ' . $this->min . ' ';
		}
		return $this->error === '';
	}
}
