<?php

namespace CsrDelft\repository\groepen\leden;

use CsrDelft\entity\groepen\KetzerDeelnemer;
use CsrDelft\repository\AbstractGroepLedenRepository;
use Doctrine\Persistence\ManagerRegistry;

class KetzerDeelnemersRepository extends AbstractGroepLedenRepository {
	public function __construct(ManagerRegistry $managerRegistry, $entityClass = KetzerDeelnemer::class) {
		parent::__construct($managerRegistry, $entityClass);
	}
}
