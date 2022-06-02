<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\repository\GroepRepository;
use Doctrine\Persistence\ManagerRegistry;

class KetzersRepository extends GroepRepository
{
	public function __construct(ManagerRegistry $registry, $entityClass = Ketzer::class)
	{
		parent::__construct($registry, $entityClass);
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
