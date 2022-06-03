<?php

namespace CsrDelft\view\formulier\keuzevelden;

use CsrDelft\view\formulier\invoervelden\InputField;

/**
 * @author Jan Pieter Waagmeester <jieter@jpwaag.com>
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class MultiSelectField extends InputField {

	private $selects = array();

	public function __construct($name, $value, $description, $keuzeopties) {
		parent::__construct($name, str_replace('&amp;&amp;', '&&', $value), $description);

		// Splits keuzes
		$selects = explode('&&', str_replace('&amp;&amp;', '&&', $keuzeopties));
		$gekozen = explode('&&', $this->value);

		foreach ($selects as $i => $opties) {

			// Splits mogelijkheden per keuze
			$opties = explode('|', $opties);
			if (isset($gekozen[$i])) {
				$keuze = $gekozen[$i];
			} else {
				$keuze = $opties[0];
			}

			// Value == label
			$values = array();
			foreach ($opties as $value) {
				$values[$value] = $value;
			}
			$this->selects[$i] = new SelectField($name . '[]', $keuze, null, $values);
		}
	}

	public function isPosted() {
		return isset($_POST[$this->name]);
	}

	public function getValue() {
		$this->value = parent::getValue();
		if ($this->isPosted()) {
			$values = filter_input(INPUT_POST, $this->name, FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
			$this->value = implode('&&', $values);
		}
		return $this->value;
	}

	public function getHtml() {
		$html = '<div class="input-group">';
		foreach ($this->selects as $select) {
			if ($this->hidden) {
				$select->css_classes[] = 'verborgen';
			}
			$html .= $select->getHtml(false);
		}
		return $html . '</div>';
	}

}
