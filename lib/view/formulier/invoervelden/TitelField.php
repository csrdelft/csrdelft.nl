<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\model\bibliotheek\BoekModel;

class TitelField extends RequiredTextField {

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if (BoekModel::existsTitel($this->value)) {
			$this->error = 'Titel bestaat al.';
		}
		return $this->error == '';
	}

}
