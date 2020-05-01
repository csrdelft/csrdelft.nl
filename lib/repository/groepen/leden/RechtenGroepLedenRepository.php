<?php

namespace CsrDelft\repository\groepen\leden;

use CsrDelft\entity\groepen\RechtenGroepLid;
use CsrDelft\repository\AbstractGroepLedenRepository;
use Doctrine\Persistence\ManagerRegistry;

class RechtenGroepLedenRepository extends AbstractGroepLedenRepository {
	public function __construct(ManagerRegistry $managerRegistry) {
		parent::__construct($managerRegistry, RechtenGroepLid::class);
	}
}
