<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\model\ProfielModel;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
class DuckField extends TextField {

	public function __construct($name, $value) {
		parent::__construct($name, $value, 'Duckstad-naam');
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		// check met strtolower is toegevoegd omdat je anders je eigen nick niet van case kan veranderen
		// doordat duckExists case-insensitive zoekt
		if (ProfielModel::instance()->existsDuck($this->value) AND strtolower($this->value) !== strtolower($this->origvalue)) {
			$this->error = 'Deze Duckstad-naam is al in gebruik';
		}
		return $this->error === '';
	}

}
