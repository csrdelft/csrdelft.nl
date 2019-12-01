<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\entity\groepen\Werkgroep;
use CsrDelft\model\security\AccessModel;


class WerkgroepenModel extends KetzersModel {
	public function __construct(AccessModel $accessModel) {
		parent::__static();
		parent::__construct($accessModel);
	}
	const ORM = Werkgroep::class;
}
