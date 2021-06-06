<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\view\formulier\invoervelden\InputField;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * SelectField
 * HTML select met opties.
 */
class SelectField extends InputField {

	public $size;
	public $multiple;
	protected $options;

	public function __construct($name, $value, $description, array $options, $size = 1, $multiple = false) {
		parent::__construct($name, $value, $description);
		$this->options = $options;
		$this->size = (int)$size;
		$this->multiple = $multiple;

		$this->css_classes = ['form-select'];
	}

	public function getOptions() {
		return $this->options;
	}

	public function getValue() {
		$this->value = parent::getValue();
		if ($this->empty_null AND $this->value == '') {
			return null;
		}
		return $this->value;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}

		if ($this->multiple) {
			if (($this->required || $this->getValue() !== null) && array_intersect($this->value, $this->options) !== $this->value) {
				$this->error = 'Onbekende optie gekozen';
			}
		} else {
			if (($this->required || $this->getValue() !== null) && !array_key_exists($this->value, $this->options)) {
				$this->error = 'Onbekende optie gekozen';
			}
		}
		return $this->error === '';
	}

	public function getHtml($include_hidden = true) {
		$html = '';
		if ($include_hidden) {
			$html .= '<input type="hidden" name="' . $this->name . '" value="" />';
		}
		$html .= '<select name="' . $this->name;
		if ($this->multiple) {
			$html .= '[]" multiple';
		} else {
			$html .= '"';
		}
		if ($this->size > 1) {
			$html .= ' size="' . $this->size . '"';
		}
		$html .= $this->getInputAttribute(array('id', 'origvalue', 'class', 'disabled', 'readonly')) . '>';
		$html .= $this->getOptionsHtml($this->options);
		return $html . '</select>';
	}

	protected function getOptionsHtml(array $options) {
		$html = '';
		foreach ($options as $value => $description) {
			$html .= '<option value="' . $value . '"';
			if ($value == $this->value) {
				$html .= ' selected="selected"';
			}
			$html .= '>' . str_replace('&amp;', '&', htmlspecialchars($description)) . '</option>';
		}
		if ($this->value == '') {
			$html .= "<option hidden disabled selected value=''></option>";
		}
		return $html;
	}

}
