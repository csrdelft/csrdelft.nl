<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\repository\bibliotheek\BoekRepository;

/**
 */
class TitelField extends TextField {
  public $required = true;

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		$boekRepository = ContainerFacade::getContainer()->get(BoekRepository::class);
		if ($boekRepository->existsTitel($this->value)) {
			$this->error = 'Titel bestaat al.';
		}
		return $this->error == '';
	}

}
