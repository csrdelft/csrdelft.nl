<?php

namespace CsrDelft\view\formulier\invoervelden;

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
