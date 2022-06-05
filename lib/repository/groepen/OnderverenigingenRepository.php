<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\entity\groepen\enum\OnderverenigingStatus;
use CsrDelft\entity\groepen\Ondervereniging;
use CsrDelft\repository\GroepRepository;
use CsrDelft\service\security\LoginService;
use Doctrine\Persistence\ManagerRegistry;

class OnderverenigingenRepository extends GroepRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Ondervereniging::class);
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
			getDatetime() .
			'[/reldate][/div][hr]';
		return $ondervereniging;
	}

	public function overzicht(string $soort = null)
	{
		if ($soort && OnderverenigingStatus::isValidValue($soort)) {
			return $this->findBy([
				'status' => GroepStatus::HT(),
				'onderverenigingStatus' => OnderverenigingStatus::from($soort),
			]);
		}
		return parent::overzicht($soort);
	}

	public function beheer(string $soort = null)
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
