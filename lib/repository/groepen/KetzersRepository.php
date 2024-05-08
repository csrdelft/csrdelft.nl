<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\repository\GroepRepository;

class KetzersRepository extends GroepRepository
{
	public function getEntityClassName(): string
	{
		return Ketzer::class;
	}

	public function nieuw($soort = null)
	{
		/** @var Ketzer $ketzer */
		$ketzer = parent::nieuw();
		$ketzer->aanmeldLimiet = null;
		$ketzer->aanmeldenVanaf = date_create_immutable();
		$ketzer->aanmeldenTot = null;
		$ketzer->bewerkenTot = null;
		$ketzer->afmeldenTot = null;
		return $ketzer;
	}
}
