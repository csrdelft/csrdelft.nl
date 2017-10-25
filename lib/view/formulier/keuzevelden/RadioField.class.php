<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\view\formulier\elementen\FormElement;

/**
 * RadioField.class.php
 *
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * KeuzeRondjeField
 * Zelfde soort mogelijkheden als een SelectField, maar dan minder klikken
 *
 * is valid als één van de opties geselecteerd is
 */
class RadioField extends SelectField {

	public $type = 'radio';
	public $columns = 0;

	public function __construct($name, $value, $description, array $options) {
		parent::__construct($name, $value, $description, $options, array(), 1, false);
	}

	public function getHtml() {
		$html = '<div class="KeuzeRondjeOptions columns-' . $this->columns . ($this->description ? '' : ' breed') . '">';
		foreach ($this->options as $value => $description) {
			$html .= $this->getOptionHtml($value, $description);
		}
		return $html . '</div>';
	}

	protected function getOptionHtml($value, $description) {
		$html = '<input id="' . $this->getId() . 'Option_' . $value . '" value="' . $value . '" ' . $this->getInputAttribute(array('type', 'name', 'class', 'origvalue', 'disabled', 'readonly', 'onclick'));
		if ($value === $this->value) {
			$html .= ' checked="checked"';
		}
		$html .= '> ';
		if ($description instanceof FormElement) {
			$html .= $description->getHtml();
		} else {
			$html .= '<label for="' . $this->getId() . 'Option_' . $value . '" class="KeuzeRondjeLabel">' . htmlspecialchars($description) . '</label>';
		}
		if ($this->columns) {
			$html .= '<br />';
		}
		return $html;
	}

	public function getJavascript() {
		$js = parent::getJavascript();
		foreach ($this->options as $value => $description) {
			if ($description instanceof FormElement) {
				$js .= $description->getJavascript();
			}
		}
		return $js;
	}

}
