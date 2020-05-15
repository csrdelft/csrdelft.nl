<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\GroepStatus;
use CsrDelft\entity\groepen\Ondervereniging;
use CsrDelft\entity\groepen\OnderverenigingStatus;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\AbstractGroepenRepository;
use CsrDelft\repository\security\AccessRepository;
use Doctrine\Persistence\ManagerRegistry;

class OnderverenigingenRepository extends AbstractGroepenRepository {
	public function __construct(AccessRepository $accessRepository, ManagerRegistry $registry) {
		parent::__construct($accessRepository, $registry, Ondervereniging::class);
	}

	public function nieuw($soort = null) {
		/** @var Ondervereniging $ondervereniging */
		$ondervereniging = parent::nieuw();
		$ondervereniging->status = GroepStatus::FT();
		$ondervereniging->soort = OnderverenigingStatus::AdspirantOndervereniging;
		$ondervereniging->status_historie = '[div]Aangemaakt als ' . $ondervereniging->status->getDescription() . ' door [lid=' . LoginModel::getUid() . '] op [reldate]' . getDatetime() . '[/reldate][/div][hr]';
		return $ondervereniging;
	}
}
