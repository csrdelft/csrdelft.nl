<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\Ketzer;
use CsrDelft\entity\groepen\KetzerSelector;
use CsrDelft\model\security\AccessModel;
use CsrDelft\repository\AbstractGroepenRepository;
use Doctrine\Persistence\ManagerRegistry;

class KetzerSelectorsRepository extends AbstractGroepenRepository {
	public function __construct(AccessModel $accessModel, ManagerRegistry $managerRegistry) {
		parent::__construct($accessModel, $managerRegistry, KetzerSelector::class);
	}

	const ORM = KetzerSelector::class;

	public function getSelectorsVoorKetzer(Ketzer $ketzer) {
		return $this->prefetch('ketzer_id = ?', [$ketzer->id]);
	}

}
