<?php

namespace CsrDelft\model\maalcie;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdAbonnementenRepository;

/**
 * MaaltijdRepetitiesModel.class.php  |  P.W.G. Brussee (brussee@live.nl)
 *
 */
class MaaltijdRepetitiesModel extends PersistenceModel {

	const ORM = MaaltijdRepetitie::class;

	protected $default_order = '(periode_in_dagen = 0) ASC, periode_in_dagen ASC, dag_vd_week ASC, standaard_titel ASC';
	/**
	 * @var MaaltijdAanmeldingenRepository
	 */
	private $maaltijdAanmeldingenRepository;

	public function __construct(MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository) {
		parent::__construct();
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
	}

	/**
	 * Filtert de repetities met het abonnement-filter van de maaltijd-repetitie op de permissies van het ingelogde lid.
	 *
	 * @param string $uid
	 * @return MaaltijdRepetitie[]
	 * @internal param MaaltijdRepetitie[] $repetities
	 */
	public function getAbonneerbareRepetitiesVoorLid($uid) {
		$repetities = $this->find('abonneerbaar = true');
		$result = array();
		foreach ($repetities as $repetitie) {
			if ($this->maaltijdAanmeldingenRepository->checkAanmeldFilter($uid, $repetitie->abonnement_filter)) {
				$result[$repetitie->mlt_repetitie_id] = $repetitie;
			}
		}
		return $result;
	}

	public function getAlleRepetities($groupById = false) {
		$repetities = $this->find();
		if ($groupById) {
			$result = array();
			foreach ($repetities as $repetitie) {
				$result[$repetitie->mlt_repetitie_id] = $repetitie;
			}
			return $result;
		}
		return $repetities;
	}

	/**
	 * @param $mrid
	 * @return MaaltijdRepetitie
	 * @throws CsrGebruikerException
	 */
	public function getRepetitie($mrid) {
		$repetitie = $this->retrieveByPrimaryKey(array($mrid));
		if ($repetitie === false) {
			throw new CsrGebruikerException('Get maaltijd-repetitie faalt: Not found $mrid =' . $mrid);
		}
		return $repetitie;
	}

	/**
	 * @param $repetitie MaaltijdRepetitie
	 * @return array
	 */
	public function saveRepetitie($repetitie) {
		return Database::transaction(function () use ($repetitie) {
			$abos = 0;
			if ($repetitie->mlt_repetitie_id == null) {
				$repetitie->mlt_repetitie_id = $this->create($repetitie);
			} else {
				$this->update($repetitie);
				if (!$repetitie->abonneerbaar) { // niet (meer) abonneerbaar
					$abos = ContainerFacade::getContainer()->get(MaaltijdAbonnementenRepository::class)->verwijderAbonnementen($repetitie->mlt_repetitie_id);
				}
			}
			return $abos;
		});
	}

	public function verwijderRepetitie($mrid) {
		if (!is_numeric($mrid) || $mrid <= 0) {
			throw new CsrGebruikerException('Verwijder maaltijd-repetitie faalt: Invalid $mrid =' . $mrid);
		}
		if (CorveeRepetitiesModel::instance()->existMaaltijdRepetitieCorvee($mrid)) {
			throw new CsrGebruikerException('Ontkoppel of verwijder eerst de bijbehorende corvee-repetities!');
		}
		if (MaaltijdenModel::instance()->existRepetitieMaaltijden($mrid)) {
			MaaltijdenModel::instance()->verwijderRepetitieMaaltijden($mrid); // delete maaltijden first (foreign key)
			throw new CsrGebruikerException('Alle bijbehorende maaltijden zijn naar de prullenbak verplaatst. Verwijder die eerst!');
		}
		$aantalAbos = ContainerFacade::getContainer()->get(MaaltijdAbonnementenRepository::class)->verwijderAbonnementen($mrid);
		$this->deleteByPrimaryKey(array($mrid));
		return $aantalAbos;
	}
}
