<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\model\security\AccessModel;
use CsrDelft\repository\AbstractGroepenRepository;
use Doctrine\Persistence\ManagerRegistry;

class KetzersRepository extends AbstractGroepenRepository {
	public function __construct(AccessModel $accessModel, ManagerRegistry $registry, $entityClass = Ketzer::class) {
		parent::__construct($accessModel, $registry, $entityClass);
	}

	public function nieuw($soort = null) {
		/** @var Ketzer $ketzer */
		$ketzer = parent::nieuw();
		$ketzer->aanmeld_limiet = null;
		$ketzer->aanmelden_vanaf = date_create_immutable();
		$ketzer->aanmelden_tot = null;
		$ketzer->bewerken_tot = null;
		$ketzer->afmelden_tot = null;
		return $ketzer;
	}
}
