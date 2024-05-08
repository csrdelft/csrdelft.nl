<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\view\formulier\invoervelden\InputField;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class TimeField extends InputField
{
	protected $minutensteps;

	public function __construct($name, $value, $description, $minutensteps = null)
	{
		parent::__construct($name, $value, $description);
		if ($minutensteps === null) {
			$this->minutensteps = 1;
		} else {
			$this->minutensteps = (int) $minutensteps;
		}
	}

	public function isPosted(): bool
	{
		return isset($_POST[$this->name . '_uur'], $_POST[$this->name . '_minuut']);
	}

	public function getUur(): string
	{
		return $_POST[$this->name . '_uur'];
	}

	public function getMinuut(): string
	{
		return $_POST[$this->name . '_minuut'];
	}

	public function getValue(): string
	{
		$this->value = parent::getValue();
		if ($this->isPosted()) {
			$this->value = $this->getUur() . ':' . $this->getMinuut();
		}
		return $this->value;
	}

	public function validate(): bool
	{
		if (!parent::validate()) {
			return false;
		}
		$uren = (int) substr($this->value, 0, 2);
		$minuten = (int) substr($this->value, 3, 5);
		if (
			!preg_match('/^(\d\d?):(\d\d?)$/', $this->value) or
			$uren < 0 or
			$uren > 23 or
			$minuten < 0 or
			$minuten > 59
		) {
			$this->error = 'Ongeldig tijdstip';
		}
		return $this->error === '';
	}

	public function getHtml()
	{
		$hours = range(0, 23);
		$minutes = range(0, 59, $this->minutensteps);

		$html =
			'<select id="' .
			$this->getId() .
			'_uur" name="' .
			$this->name .
			'_uur" origvalue="' .
			substr($this->origvalue, 0, 2) .
			'" ' .
			$this->getInputAttribute('class') .
			'>';
		foreach ($hours as $value) {
			$value = sprintf('%02d', $value);
			$html .= '<option value="' . $value . '"';
			if ($value == substr($this->value, 0, 2)) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . $value . '</option>';
		}
		$html .= '</select> ';

		$html .=
			'<select id="' .
			$this->getId() .
			'_minuut" name="' .
			$this->name .
			'_minuut" origvalue="' .
			substr($this->origvalue, 3, 2) .
			'" ' .
			$this->getInputAttribute('class') .
			'>';
		$previousvalue = 0;
		foreach ($minutes as $value) {
			$value = sprintf('%02d', $value);
			$html .= '<option value="' . $value . '"';
			if ($value > $previousvalue && $value <= substr($this->value, 3, 2)) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . $value . '</option>';
			$previousvalue = $value;
		}
		return $html . '</select>';
	}
}
