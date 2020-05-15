<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Werkgroep;
use CsrDelft\repository\security\AccessRepository;
use Doctrine\Persistence\ManagerRegistry;


class WerkgroepenRepository extends KetzersRepository {
	public function __construct(AccessRepository $accessRepository, ManagerRegistry $registry) {
		parent::__construct($accessRepository, $registry, Werkgroep::class);
	}
}
