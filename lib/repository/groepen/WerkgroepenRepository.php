<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Werkgroep;
use CsrDelft\repository\security\AccessRepository;
use Doctrine\Persistence\ManagerRegistry;


class WerkgroepenRepository extends KetzersRepository {
	public function __construct(AccessRepository $accessModel, ManagerRegistry $registry) {
		parent::__construct($accessModel, $registry, Werkgroep::class);
	}
}
