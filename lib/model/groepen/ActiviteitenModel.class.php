<?php
require_once 'model/groepen/KetzersModel.class.php';

class ActiviteitenModel extends KetzersModel {

	const ORM = Activiteit::class;

	protected static $instance;

	public function nieuw($soort = null) {
		if (!in_array($soort, ActiviteitSoort::getTypeOptions())) {
			$soort = ActiviteitSoort::SjaarsActie;
		}
		$activiteit = parent::nieuw();
		$activiteit->soort = $soort;
		$activiteit->rechten_aanmelden = null;
		$activiteit->locatie = null;
		$activiteit->in_agenda = true;
		return $activiteit;
	}

}
