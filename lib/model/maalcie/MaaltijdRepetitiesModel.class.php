<?php

require_once 'model/entity/maalcie/MaaltijdRepetitie.class.php';
require_once 'model/maalcie/MaaltijdAbonnementenModel.class.php';
require_once 'model/maalcie/CorveeRepetitiesModel.class.php';

/**
 * MaaltijdRepetitiesModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MaaltijdRepetitiesModel extends PersistenceModel {

    const ORM = 'MaaltijdRepetitie';
    const DIR = 'maalcie/';

    protected $default_order = 'periode_in_dagen ASC, dag_vd_week ASC';

    protected static $instance;
    
    public function nieuwMaaltijdRepetitie($mrid = 0, $dag = null, $periode = null, $titel = '', $tijd = null, $prijs = null, $abo = null, $limiet = null, $filter = null) {
        $repetitie = new MaaltijdRepetitie();
        $this->mlt_repetitie_id = (int) $mrid;
        if ($dag === null) {
            $dag = intval(Instellingen::get('maaltijden', 'standaard_repetitie_weekdag'));
        }
        $repetitie->dag_vd_week = $dag;
        if ($periode === null) {
            $periode = intval(Instellingen::get('maaltijden', 'standaard_repetitie_periode'));
        }
        $repetitie->periode_in_dagen = $periode;
        $repetitie->standaard_titel = $titel;
        if ($tijd === null) {
            $tijd = Instellingen::get('maaltijden', 'standaard_aanvang');
        }
        $repetitie->standaard_tijd = $tijd;
        if ($prijs === null) {
            $prijs = intval(Instellingen::get('maaltijden', 'standaard_prijs'));
        }
        $repetitie->standaard_prijs = $prijs;
        if ($abo === null) {
            $abo = (boolean) Instellingen::get('maaltijden', 'standaard_abonneerbaar');
        }
        $repetitie->abonneerbaar = $abo;
        if ($limiet === null) {
            $limiet = intval(Instellingen::get('maaltijden', 'standaard_limiet'));
        }
        $repetitie->standaard_limiet = $limiet;
        $repetitie->abonnement_filter = $filter;

        return $repetitie;
    }

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
			if (MaaltijdAanmeldingenModel::checkAanmeldFilter($uid, $repetitie->abonnement_filter)) {
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
		if (!is_int($mrid) || $mrid <= 0) {
			throw new Exception('Get maaltijd-repetitie faalt: Invalid $mrid =' . $mrid);
		}
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
	}

	public function verwijderRepetitie($mrid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new Exception('Verwijder maaltijd-repetitie faalt: Invalid $mrid =' . $mrid);
		}
		if (CorveeRepetitiesModel::existMaaltijdRepetitieCorvee($mrid)) {
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
