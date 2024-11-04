<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Commissie;
use CsrDelft\entity\groepen\enum\CommissieSoort;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\repository\GroepRepository;

class CommissiesRepository extends GroepRepository
{
	public function getEntityClassName()
	{
		return Commissie::class;
	}

	public function nieuw($soort = null)
	{
		if (is_string($soort)) {
			$soort = $this->parseSoort($soort);
		}
		if ($soort == null) {
			$soort = CommissieSoort::Commissie();
		}
		/** @var Commissie $commissie */
		$commissie = parent::nieuw();
		$commissie->commissieSoort = $soort;
		return $commissie;
	}

	public function parseSoort(string $soort = null)
	{
		if ($soort && CommissieSoort::isValidValue($soort)) {
			return CommissieSoort::from($soort);
		}
		return parent::parseSoort($soort);
	}
}
