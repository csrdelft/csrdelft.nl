<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\view\formulier\invoervelden\RequiredTextField;

class TitelField extends RequiredTextField {

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if (BiebCatalogus::existsProperty('titel', $this->getValue())) {
			$this->error = 'Titel bestaat al.';
		}
		return $this->error == '';
	}

}
