<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Activiteit;
use CsrDelft\entity\groepen\enum\ActiviteitSoort;
use CsrDelft\entity\groepen\enum\GroepStatus;

class ActiviteitenRepository extends KetzersRepository
{
	public function getEntityClassName()
	{
		return Activiteit::class;
	}

	public function nieuw($soort = null)
	{
		if (is_string($soort)) {
			$soort = $this->parseSoort($soort);
		}
		if ($soort == null) {
			$soort = ActiviteitSoort::Vereniging();
		}
		/** @var Activiteit $activiteit */
		$activiteit = parent::nieuw();
		$activiteit->activiteitSoort = $soort;
		$activiteit->rechtenAanmelden = null;
		$activiteit->locatie = null;
		$activiteit->inAgenda = false;
		return $activiteit;
	}

	public function parseSoort(string $soort = null)
	{
		if ($soort && ActiviteitSoort::isValidValue($soort)) {
			return ActiviteitSoort::from($soort);
		}
		return parent::parseSoort($soort);
	}
}
