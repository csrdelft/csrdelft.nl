<?php

namespace CsrDelft\model\bibliotheek;

use CsrDelft\view\formulier\Formulier;

/**
 * Werkomheen
 */
class BiebFormulier extends Formulier {

	public function __construct() {
		parent::__construct(null, null);
	}

	public function getScriptTag() {
		parent::getScriptTag();
	}

}
