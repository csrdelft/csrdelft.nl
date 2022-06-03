<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\enum\HuisStatus;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\repository\GroepRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;

class WoonoordenRepository extends GroepRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Woonoord::class);
	}

	public function nieuw($soort = null) {
		/** @var Woonoord $woonoord */
		$woonoord = parent::nieuw();
		$woonoord->huisStatus = HuisStatus::Woonoord();
		$woonoord->status_historie = '[div]Aangemaakt als ' . HuisStatus::Woonoord()->getDescription() . ' door [lid=' . LoginService::getUid() . '] op [reldate]' . getDatetime() . '[/reldate][/div][hr]';
		return $woonoord;
	}

	public function overzicht(string $soort = null)
	{
		if ($soort && HuisStatus::isValidValue($soort)) {
			return $this->findBy(['status' => GroepStatus::HT(), 'huisStatus' => HuisStatus::from($soort)]);
		}
		return parent::overzicht($soort);
	}

	public function beheer(string $soort = null)
	{
		if ($soort && HuisStatus::isValidValue($soort)) {
			return $this->findBy(['huisStatus' => HuisStatus::from($soort)]);
		}
		return parent::beheer($soort);
	}

	public function parseSoort(string $soort = null)
	{
		if ($soort && HuisStatus::isValidValue($soort)) {
			return HuisStatus::from($soort);
		}
		return parent::parseSoort($soort);
	}
}
