<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\KetzerOptie;
use CsrDelft\entity\groepen\KetzerSelector;
use CsrDelft\model\security\AccessModel;
use CsrDelft\repository\AbstractGroepenRepository;
use Doctrine\Persistence\ManagerRegistry;

class KetzerOptiesRepository extends AbstractGroepenRepository {
	public function __construct(AccessModel $accessModel, ManagerRegistry $managerRegistry) {
		parent::__construct($accessModel, $managerRegistry, KetzerOptie::class);
	}

	const ORM = KetzerOptie::class;

	public function getOptiesVoorSelect(KetzerSelector $select) {
		return $this->findBy(['select_id' => $select->select_id]);
	}
}
