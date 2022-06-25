<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\view\formulier\invoervelden\InputField;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * DateField
 *
 * Selecteer een datum, met een mogelijk maximum jaar.
 *
 * Produceert drie velden.
 */
class DateField extends InputField
{
	protected $max_jaar;
	protected $min_jaar;

	public function __construct(
		$name,
		$value,
		$description,
		$maxyear = null,
		$minyear = null
	) {
		parent::__construct($name, $value, $description);
		if (is_int($maxyear)) {
			$this->max_jaar = $maxyear;
		} else {
			$this->max_jaar = (int) date('Y') + 10;
		}
		if (is_int($minyear)) {
			$this->min_jaar = $minyear;
		} else {
			$this->min_jaar = (int) date('Y') - 10;
		}
		$jaar = (int) date('Y', strtotime($value));
		if ($jaar > $this->max_jaar) {
			$this->max_jaar = $jaar;
		}
		if ($jaar < $this->min_jaar) {
			$this->min_jaar = $jaar;
		}

		$this->css_classes[] = 'DateField';
	}

	public function validate()
	{
		if (!parent::validate()) {
			return false;
		}

		$date = \DateTimeImmutable::createFromFormat('!YYYY-MM-DD', $this->value);

		if ($this->value == '0000-00-00' or empty($this->value)) {
			if ($this->required) {
				$this->error = 'Dit is een verplicht veld';
			}
		} elseif ($date === false) {
			$this->error = 'Ongeldige datum';
		} elseif (
			is_int($this->max_jaar) and
			intval($date->format('Y')) > $this->max_jaar
		) {
			$this->error = 'Kies een jaar voor ' . $this->max_jaar;
		} elseif (
			is_int($this->min_jaar) and
			intval($date->format('Y')) < $this->min_jaar
		) {
			$this->error = 'Kies een jaar na ' . $this->min_jaar;
		}

		return $this->error === '';
	}

	public function getHtml()
	{
		$attributes = $this->getInputAttribute([
			'id',
			'name',
			'class',
			'value',
			'origvalue',
			'disabled',
			'readonly',
			'maxlength',
			'placeholder',
			'autocomplete',
		]);

		$minValue = $maxValue = null;

		if ($this->min_jaar) {
			$minValue = $this->min_jaar . '-01-01';
		}

		if ($this->max_jaar) {
			$maxValue = $this->max_jaar + 1 . '-01-01';
		}

		return <<<HTML
<input
 {$attributes}
 type="date"
 min="{$minValue}"
 max="{$maxValue}"
 pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}"
/>
HTML;
	}
}
