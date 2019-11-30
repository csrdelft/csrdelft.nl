<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\RechtenGroep;

class RechtenGroepenModel extends AbstractGroepenModel {
	public function __construct() {
		parent::__static();
		parent::__construct();
	}

	const ORM = RechtenGroep::class;

	public function nieuw($soort = null) {
		/** @var RechtenGroep $groep */
		$groep = parent::nieuw();
		$groep->rechten_aanmelden = P_LEDEN_MOD;
		return $groep;
	}

	public static function getNaam() {
		return 'overig';
	}

}
