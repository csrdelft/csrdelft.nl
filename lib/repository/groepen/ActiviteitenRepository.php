<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\enum\ActiviteitSoort;
use Doctrine\Persistence\ManagerRegistry;


class ActiviteitenRepository extends KetzersRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Activiteit::class);
	}

	public function nieuw($soort = null) {
		if ($soort == null) {
			$soort = ActiviteitSoort::SjaarsActie()->getValue();
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
