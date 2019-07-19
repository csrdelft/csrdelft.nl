<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\Ketzer;

class KetzersModel extends AbstractGroepenModel {

	const ORM = Ketzer::class;

	public function nieuw($soort = null) {
		/** @var Ketzer $ketzer */
		$ketzer = parent::nieuw();
		$ketzer->aanmeld_limiet = null;
		$ketzer->aanmelden_vanaf = getDateTime();
		$ketzer->aanmelden_tot = null;
		$ketzer->bewerken_tot = null;
		$ketzer->afmelden_tot = null;
		return $ketzer;
	}

}
