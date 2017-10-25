<?php

namespace CsrDelft\model\groepen\leden;

use CsrDelft\model\entity\groepen\WerkgroepDeelnemer;
use CsrDelft\model\groepen;

class WerkgroepDeelnemersModel extends groepen\leden\KetzerDeelnemersModel {
	const ORM = WerkgroepDeelnemer::class;
}
