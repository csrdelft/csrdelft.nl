<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\entity\groepen\Werkgroep;


class WerkgroepenModel extends KetzersModel {
	public function __construct() {
		parent::__static();
		parent::__construct();
	}
	const ORM = Werkgroep::class;
}
