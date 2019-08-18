<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\model\bibliotheek\BoekModel;

/**
 */
class TitelField extends TextField {
  public $required = true;

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if (BoekModel::instance()->existsTitel($this->value)) {
			$this->error = 'Titel bestaat al.';
		}
		return $this->error == '';
	}

}
