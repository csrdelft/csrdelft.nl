<?php

namespace CsrDelft\view\formulier\invoervelden;

/**
 * UrlField.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * UrlField checked of de invoer op een url lijkt.
 */
class UrlField extends TextField {

	public function getValue() {
		$this->value = parent::getValue();
		if (startsWith($this->value, CSR_ROOT)) {
			$this->value = str_replace(CSR_ROOT, '', $this->value);
		}
		return $this->value;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		// controleren of het een geldige url is
		if (!url_like($this->value) AND !startsWith($this->value, '/')) {
			$this->error = 'Geen geldige url';
		}
		return $this->error === '';
	}

}
