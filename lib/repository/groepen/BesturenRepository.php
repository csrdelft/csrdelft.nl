<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Bestuur;
use CsrDelft\repository\AbstractGroepenRepository;
use CsrDelft\repository\security\AccessRepository;
use Doctrine\Persistence\ManagerRegistry;

class BesturenRepository extends AbstractGroepenRepository {
	public function __construct(AccessRepository $accessModel, ManagerRegistry $registry) {
		parent::__construct($accessModel, $registry, Bestuur::class);
	}

	public function nieuw($soort = null) {
		/** @var Bestuur $bestuur */
		$bestuur = parent::nieuw();
		$bestuur->bijbeltekst = '';
		return $bestuur;
	}
}
