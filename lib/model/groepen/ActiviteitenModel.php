<?php

namespace CsrDelft\model\groepen;

use CsrDelft\model\entity\groepen\Activiteit;
use CsrDelft\model\entity\groepen\ActiviteitSoort;


class ActiviteitenModel extends KetzersModel {

	const ORM = Activiteit::class;

	public function nieuw($soort = null) {
		if (!in_array($soort, ActiviteitSoort::getTypeOptions())) {
			$soort = ActiviteitSoort::SjaarsActie;
		}
		/** @var Activiteit $activiteit */
		$activiteit = parent::nieuw();
		$activiteit->soort = $soort;
		$activiteit->rechten_aanmelden = null;
		$activiteit->locatie = null;
		$activiteit->in_agenda = false;
		return $activiteit;
	}

}
