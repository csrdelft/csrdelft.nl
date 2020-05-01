<?php

namespace CsrDelft\repository\groepen\leden;

use CsrDelft\entity\groepen\WerkgroepDeelnemer;
use Doctrine\Persistence\ManagerRegistry;

class WerkgroepDeelnemersRepository extends KetzerDeelnemersRepository {
	public function __construct(ManagerRegistry $managerRegistry) {
		parent::__construct($managerRegistry, WerkgroepDeelnemer::class);
	}
}
