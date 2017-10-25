<?php

namespace CsrDelft\view\formulier\invoervelden;
/**
 * TextareaField.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * Een Textarea die groter wordt als de inhoud niet meer in het veld past.
 */
class TextareaField extends TextField {

	public function __construct($name, $value, $description, $rows = 2, $max_len = null, $min_len = null) {
		parent::__construct($name, $value, $description, $max_len, $min_len);
		if (is_int($rows)) {
			$this->rows = $rows;
		}
		$this->css_classes[] = 'AutoSize';
		$this->css_classes[] = 'textarea-transition';
	}

	public function getHtml() {
		return '<textarea' . $this->getInputAttribute(array('id', 'name', 'origvalue', 'class', 'disabled', 'readonly', 'placeholder', 'maxlength', 'rows', 'autocomplete')) . '>' . $this->value . '</textarea>';
	}

	/**
	 * Maakt een verborgen div met dezelfde eigenschappen als de textarea en
	 * gebruikt autoresize eigenschappen van de div om de hoogte te bepalen voor de textarea.
	 *
	 * @return string
	 */
	public function getJavascript() {
		return parent::getJavascript() . <<<JS

$('#{$this->getId()}').autosize();
JS;
	}

}
