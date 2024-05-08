<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\common\Util\DateUtil;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\enum\OnderverenigingStatus;
use CsrDelft\entity\groepen\Ondervereniging;
use CsrDelft\repository\GroepRepository;
use CsrDelft\service\security\LoginService;

class OnderverenigingenRepository extends GroepRepository
{
	public function getEntityClassName(): string
	{
		return Ondervereniging::class;
	}

	public function nieuw($soort = null)
	{
		/** @var Ondervereniging $ondervereniging */
		$ondervereniging = parent::nieuw();
		$ondervereniging->status = GroepStatus::FT();
		$ondervereniging->onderverenigingStatus = OnderverenigingStatus::AdspirantOndervereniging();
		$ondervereniging->status_historie =
			'[div]Aangemaakt als ' .
			$ondervereniging->status->getDescription() .
			' door [lid=' .
			LoginService::getUid() .
			'] op [reldate]' .
			DateUtil::getDatetime() .
			'[/reldate][/div][hr]';
		return $ondervereniging;
	}

	public function overzicht(
		int $limit = null,
		int $offset = null,
		string $soort = null
	): array {
		if ($soort && OnderverenigingStatus::isValidValue($soort)) {
			return $this->findBy(
				[
					'status' => GroepStatus::HT(),
					'onderverenigingStatus' => OnderverenigingStatus::from($soort),
				],
				null,
				$limit,
				$offset
			);
		}
		return parent::overzicht($limit, $offset, $soort);
	}

	public function beheer(string $soort = null): array
	{
		if ($soort && OnderverenigingStatus::isValidValue($soort)) {
			return $this->findBy([
				'onderverenigingStatus' => OnderverenigingStatus::from($soort),
			]);
		}
		return parent::beheer($soort);
	}

	public function parseSoort(string $soort = null)
	{
		if ($soort && OnderverenigingStatus::isValidValue($soort)) {
			return OnderverenigingStatus::from($soort);
		}
		return parent::parseSoort($soort);
	}
}
