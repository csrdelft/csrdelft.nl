<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Werkgroep;
use Doctrine\Persistence\ManagerRegistry;


class WerkgroepenRepository extends KetzersRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Werkgroep::class);
	}
}
