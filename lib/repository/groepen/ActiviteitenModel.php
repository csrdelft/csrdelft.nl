<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\ActiviteitSoort;
use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\model\security\AccessModel;
use Doctrine\Persistence\ManagerRegistry;


class ActiviteitenModel extends KetzersModel {
	public function __construct(AccessModel $accessModel, ManagerRegistry $registry) {
		parent::__construct($accessModel, $registry, Activiteit::class);
	}

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
