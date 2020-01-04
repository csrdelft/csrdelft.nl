<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\AbstractGroepenModel;
use CsrDelft\model\entity\groepen\Bestuur;
use CsrDelft\model\security\AccessModel;

class BesturenModel extends AbstractGroepenModel {
	public function __construct(AccessModel $accessModel) {
		parent::__static();
		parent::__construct($accessModel);
	}

	const ORM = Bestuur::class;

	public function nieuw($soort = null) {
		/** @var Bestuur $bestuur */
		$bestuur = parent::nieuw();
		$bestuur->bijbeltekst = '';
		return $bestuur;
	}
}
