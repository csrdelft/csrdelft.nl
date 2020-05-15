<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Commissie;
use CsrDelft\entity\groepen\CommissieSoort;
use CsrDelft\repository\AbstractGroepenRepository;
use CsrDelft\repository\security\AccessRepository;
use Doctrine\Persistence\ManagerRegistry;

class CommissiesRepository extends AbstractGroepenRepository {
	public function __construct(AccessRepository $accessModel, ManagerRegistry $registry) {
		parent::__construct($accessModel, $registry, Commissie::class);
	}

	public function nieuw($soort = null) {
		if (!$soort || !in_array($soort, CommissieSoort::getEnumValues())) {
			$soort = CommissieSoort::Commissie;
		}
		/** @var Commissie $commissie */
		$commissie = parent::nieuw();
		$commissie->soort = $soort;
		return $commissie;
	}
}
