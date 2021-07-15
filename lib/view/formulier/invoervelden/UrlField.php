<?php

namespace CsrDelft\view\formulier\invoervelden;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * UrlField checked of de invoer op een url lijkt.
 */
class UrlField extends LegacyTextField {

	public function getValue() {
		$this->value = parent::getValue();
		if ($this->value && str_starts_with($this->value, getCsrRoot())) {
			$this->value = str_replace(getCsrRoot(), '', $this->value);
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
		if (!url_like($this->value) && !str_starts_with($this->value, '/')) {
			$this->error = 'Geen geldige url';
		}
		return $this->error === '';
	}

}
