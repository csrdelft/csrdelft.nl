<?php

use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/maalcie/MaaltijdRepetitie.class.php';
require_once 'model/maalcie/MaaltijdAbonnementenModel.class.php';
require_once 'model/maalcie/CorveeRepetitiesModel.class.php';

/**
 * MaaltijdRepetitiesModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MaaltijdRepetitiesModel extends PersistenceModel {

    const ORM = MaaltijdRepetitie::class;
    const DIR = 'maalcie/';

    protected $default_order = '(periode_in_dagen = 0) ASC, periode_in_dagen ASC, dag_vd_week ASC, standaard_titel ASC';

    protected static $instance;

	/**
	 * Filtert de repetities met het abonnement-filter van de maaltijd-repetitie op de permissies van het ingelogde lid.
	 *
	 * @param string $uid
	 * @return MaaltijdRepetitie[]
	 * @throws Exception
	 * @internal param MaaltijdRepetitie[] $repetities
	 */
	public function getAbonneerbareRepetitiesVoorLid($uid) {
		$repetities = $this->find('abonneerbaar = true');
		$result = array();
		foreach ($repetities as $repetitie) {
			if (MaaltijdAanmeldingenModel::instance()->checkAanmeldFilter($uid, $repetitie->abonnement_filter)) {
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
     * @throws Exception
     */
	public function getRepetitie($mrid) {
		$repetitie = $this->retrieveByPrimaryKey(array($mrid));
		if ($repetitie === false) {
			throw new Exception('Get maaltijd-repetitie faalt: Not found $mrid =' . $mrid);
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
			if ($repetitie->mlt_repetitie_id === 0) {
				$repetitie->mlt_repetitie_id = $this->create($repetitie);
			} else {
				$this->update($repetitie);
				if (!$repetitie->abonneerbaar) { // niet (meer) abonneerbaar
					$abos = MaaltijdAbonnementenModel::instance()->verwijderAbonnementen($repetitie->mlt_repetitie_id);
				}
			}
			return $abos;
		});
	}

	public function verwijderRepetitie($mrid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new Exception('Verwijder maaltijd-repetitie faalt: Invalid $mrid =' . $mrid);
		}
		if (CorveeRepetitiesModel::instance()->existMaaltijdRepetitieCorvee($mrid)) {
			throw new Exception('Ontkoppel of verwijder eerst de bijbehorende corvee-repetities!');
		}
		if (MaaltijdenModel::instance()->existRepetitieMaaltijden($mrid)) {
			MaaltijdenModel::instance()->verwijderRepetitieMaaltijden($mrid); // delete maaltijden first (foreign key)
			throw new Exception('Alle bijbehorende maaltijden zijn naar de prullenbak verplaatst. Verwijder die eerst!');
		}
		$aantalAbos = MaaltijdAbonnementenModel::instance()->verwijderAbonnementen($mrid);
        $this->deleteByPrimaryKey(array($mrid));
		return $aantalAbos;
	}
}
