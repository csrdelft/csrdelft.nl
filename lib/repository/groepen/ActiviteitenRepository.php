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
			$soort = ActiviteitSoort::Vereniging()->getValue();
		}
		/** @var Activiteit $activiteit */
		$activiteit = parent::nieuw();
		$activiteit->activiteitSoort = ActiviteitSoort::from($soort);
		$activiteit->rechtenAanmelden = null;
		$activiteit->locatie = null;
		$activiteit->inAgenda = false;
		return $activiteit;
	}
}
