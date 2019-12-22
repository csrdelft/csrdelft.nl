<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\Ketzer;
use CsrDelft\model\security\AccessModel;

class KetzersModel extends AbstractGroepenModel {
	public function __construct(AccessModel $accessModel) {
		parent::__static();
		parent::__construct($accessModel);
	}

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
