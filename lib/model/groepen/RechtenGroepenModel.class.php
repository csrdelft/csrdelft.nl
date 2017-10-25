<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\RechtenGroep;

class RechtenGroepenModel extends AbstractGroepenModel {

	const ORM = RechtenGroep::class;

	public function nieuw() {
		$groep = parent::nieuw();
		$groep->rechten_aanmelden = 'P_LOGGED_IN';
		return $groep;
	}

}
