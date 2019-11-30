<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\Bestuur;

class BesturenModel extends AbstractGroepenModel {
	public function __construct() {
		parent::__static();
		parent::__construct();
	}

	const ORM = Bestuur::class;

	public function nieuw($soort = null) {
		/** @var Bestuur $bestuur */
		$bestuur = parent::nieuw();
		$bestuur->bijbeltekst = '';
		return $bestuur;
	}
}
