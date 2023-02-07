<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\enum\HuisStatus;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\repository\GroepRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;

class WoonoordenRepository extends GroepRepository
{
	public function getEntityClassName()
	{
		return Woonoord::class;
	}

	public function nieuw($soort = null)
	{
		/** @var Woonoord $woonoord */
		$woonoord = parent::nieuw();
		$woonoord->huisStatus = HuisStatus::Woonoord();
		$woonoord->status_historie =
			'[div]Aangemaakt als ' .
			HuisStatus::Woonoord()->getDescription() .
			' door [lid=' .
			LoginService::getUid() .
			'] op [reldate]' .
			DateUtil::getDatetime() .
			'[/reldate][/div][hr]';
		return $woonoord;
	}

	public function overzicht(
		int $limit = null,
		int $offset = null,
		string $soort = null
	) {
		if ($soort && HuisStatus::isValidValue($soort)) {
			return $this->findBy(
				[
					'status' => GroepStatus::HT(),
					'huisStatus' => HuisStatus::from($soort),
				],
				null,
				$limit,
				$offset
			);
		}
		return parent::overzicht($limit, $offset, $soort);
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
