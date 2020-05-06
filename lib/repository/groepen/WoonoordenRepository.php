<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\HuisStatus;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\model\security\AccessModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\AbstractGroepenRepository;
use Doctrine\Persistence\ManagerRegistry;

class WoonoordenRepository extends AbstractGroepenRepository {
	public function __construct(AccessModel $accessModel, ManagerRegistry $registry) {
		parent::__construct($accessModel, $registry, Woonoord::class);
	}

	public function nieuw($soort = null) {
		/** @var Woonoord $woonoord */
		$woonoord = parent::nieuw();
		$woonoord->status = HuisStatus::Woonoord();
		$woonoord->status_historie = '[div]Aangemaakt als ' . HuisStatus::Woonoord()->getDescription() . ' door [lid=' . LoginModel::getUid() . '] op [reldate]' . getDatetime() . '[/reldate][/div][hr]';
		return $woonoord;
	}
}
