<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\view\formulier\FormElement;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * KeuzeRondjeField
 * Zelfde soort mogelijkheden als een SelectField, maar dan minder klikken
 *
 * is valid als één van de opties geselecteerd is
 */
class RadioField extends SelectField
{

	public $type = 'radio';

	public function __construct($name, $value, $description, array $options)
	{
		parent::__construct($name, $value, $description, $options, 1, false);

		$this->css_classes = ['FormElement', 'form-check-input'];
	}

	public function getHtml($include_hidden = true)
	{
		$html = '';
		if ($include_hidden) {
			$html .= '<input type="hidden" name="' . $this->name . '" value="" />';
		}
		$html .= '<div class="KeuzeRondjeOptions' . ($this->description ? '' : ' breed') . '">';
		foreach ($this->options as $value => $description) {
			$html .= $this->getOptionHtml($value, $description);
		}
		return $html . '</div>';
	}

	protected function getOptionHtml($value, $description)
	{
		$id = $this->getId() . 'Option_' . $value;
		$html = '<div class="form-check form-check-inline" id="' . $this->getId() . '">';
		$html .= '<input id="' . $id . '" value="' . $value . '" ' . $this->getInputAttribute(array('type', 'name', 'class', 'origvalue', 'disabled', 'readonly', 'onclick'));
		if ($value === $this->value) {
			$html .= ' checked="checked"';
		}
		$html .= '> ';
		if ($description instanceof FormElement) {
			$html .= $description->getHtml();
		} else {
			$html .= '<label for="' . $id . '" class="form-check-label">' . htmlspecialchars($description) . '</label>';
		}
		$html .= '</div>';
		return $html;
	}

	public function getJavascript()
	{
		$js = parent::getJavascript();
		foreach ($this->options as $value => $description) {
			if ($description instanceof FormElement) {
				$js .= $description->getJavascript();
			}
		}
		return $js;
	}

}
