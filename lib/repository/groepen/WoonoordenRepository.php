<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\HuisStatus;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\repository\AbstractGroepenRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;

class WoonoordenRepository extends AbstractGroepenRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Woonoord::class);
	}

	public function nieuw($soort = null) {
		/** @var Woonoord $woonoord */
		$woonoord = parent::nieuw();
		$woonoord->status = HuisStatus::Woonoord();
		$woonoord->status_historie = '[div]Aangemaakt als ' . HuisStatus::Woonoord()->getDescription() . ' door [lid=' . LoginService::getUid() . '] op [reldate]' . getDatetime() . '[/reldate][/div][hr]';
		return $woonoord;
	}
}
