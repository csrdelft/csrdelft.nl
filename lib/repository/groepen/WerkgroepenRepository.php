<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Werkgroep;
use CsrDelft\model\security\AccessModel;
use Doctrine\Persistence\ManagerRegistry;


class WerkgroepenRepository extends KetzersRepository {
	public function __construct(AccessModel $accessModel, ManagerRegistry $registry) {
		parent::__construct($accessModel, $registry, Werkgroep::class);
	}
}
