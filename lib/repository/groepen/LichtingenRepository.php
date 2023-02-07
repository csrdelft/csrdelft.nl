<?php

namespace CsrDelft\repository\groepen;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\groepen\Lichting;
use CsrDelft\repository\GroepRepository;
use CsrDelft\repository\ProfielRepository;
use Doctrine\Persistence\ManagerRegistry;

class LichtingenRepository extends GroepRepository
{
	public function getEntityClassName()
	{
		return Lichting::class;
	}

	public function get($lidjaar)
	{
		return $this->nieuw($lidjaar);
	}

	public function nieuw($lidjaar = null)
	{
		if ($lidjaar === null) {
			$lidjaar = date('Y');
		}
		/** @var Lichting $lichting */
		$lichting = parent::nieuw();
		$lichting->lidjaar = (int) $lidjaar;
		$lichting->id = $lichting->lidjaar;
		$lichting->naam = 'Lichting ' . $lichting->lidjaar;
		$lichting->familie = 'Lichting';
		$lichting->beginMoment = date_create_immutable(
			$lichting->lidjaar . '-09-01 00:00:00'
		);
		return $lichting;
	}

	public function findBy(
		array $criteria,
		array $orderBy = null,
		$limit = null,
		$offset = null
	) {
		$jongste = static::getJongsteLidjaar();
		$oudste = static::getOudsteLidjaar();
		$lichtingen = [];
		for ($lidjaar = $jongste; $lidjaar >= $oudste; $lidjaar--) {
			$lichtingen[] = $this->nieuw($lidjaar);
		}
		return $lichtingen;
	}

	public static function getHuidigeJaargang()
	{
		$jaar = (int) date('Y');
		$maand = (int) date('m');
		if ($maand < 8) {
			$jaar--;
		}
		return $jaar . '-' . ($jaar + 1);
	}

	public static function getJongsteLidjaar()
	{
		$profielRepository = ContainerFacade::getContainer()->get(
			ProfielRepository::class
		);
		return (int) $profielRepository
			->createQueryBuilder('p')
			->select('MAX(p.lidjaar)')
			->getQuery()
			->getSingleScalarResult();
	}

	public static function getOudsteLidjaar()
	{
		$profielRepository = ContainerFacade::getContainer()->get(
			ProfielRepository::class
		);

		return (int) $profielRepository
			->createQueryBuilder('p')
			->select('MIN(p.lidjaar)')
			->where('p.lidjaar > 0')
			->getQuery()
			->getSingleScalarResult();
	}
}
