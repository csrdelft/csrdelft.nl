<?php

namespace CsrDelft\view\formulier\invoervelden;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * Een TextField is een elementaire input-tag en heeft een maximale lengte.
 * HTML wordt ge-escaped.
 * Uiteraard kunnen er suggesties worden opgegeven.
 */
class TextField extends InputField {

	public function __construct($name, $value, $description, $max_len = 255, $min_len = 0, $model = null) {
		parent::__construct($name, $value === null ? $value : htmlspecialchars_decode($value), $description, $model);
		if (is_int($max_len)) {
			$this->max_len = $max_len;
		}
		if (is_int($min_len)) {
			$this->min_len = $min_len;
		}
		if ($this->isPosted()) {
			// reverse InputField constructor $this->getValue()
			$this->value = htmlspecialchars_decode($this->value);
		}
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->value !== null AND !is_utf8($this->value)) {
			$this->error = 'Ongeldige karakters, gebruik reguliere tekst';
		}
		return $this->error === '';
	}

	public function getValue() {
		$this->value = parent::getValue();
		if ($this->empty_null AND $this->value == '') {
			return null;
		}
		return htmlspecialchars($this->value);
	}

}
