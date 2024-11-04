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
	public function getEntityClassName()
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

	public function parseSoort(string $soort = null)
	{
		if ($soort && OnderverenigingStatus::isValidValue($soort)) {
			return OnderverenigingStatus::from($soort);
		}
		return parent::parseSoort($soort);
	}
}
