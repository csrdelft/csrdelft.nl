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
    }

	public static function getFirstOccurrence(MaaltijdRepetitie $repetitie) {
		$datum = time();
		$shift = $repetitie->dag_vd_week - date('w', $datum) + 7;
		$shift %= 7;
		if ($shift > 0) {
			$datum = strtotime('+' . $shift . ' days', $datum);
		}
		return date('Y-m-d', $datum);
	}

	/**
	 * Filtert de repetities met het abonnement-filter van de maaltijd-repetitie op de permissies van het ingelogde lid.
	 *
	 * @param string $uid
	 * @return MaaltijdRepetitie[]
	 * @throws Exception
	 * @internal param MaaltijdRepetitie[] $repetities
	 */
	public static function getAbonneerbareRepetitiesVoorLid($uid) {
		$repetities = self::loadRepetities('abonneerbaar = true');
		$result = array();
		foreach ($repetities as $repetitie) {
			if (MaaltijdAanmeldingenModel::checkAanmeldFilter($uid, $repetitie->abonnement_filter)) {
				$result[$repetitie->mlt_repetitie_id] = $repetitie;
			}
		}
		return $result;
	}

	public static function getAbonneerbareRepetities() {
		return self::loadRepetities('abonneerbaar = true');
	}

	public static function getAlleRepetities($groupById = false) {
		$repetities = self::loadRepetities();
		if ($groupById) {
			$result = array();
			foreach ($repetities as $repetitie) {
				$result[$repetitie->mlt_repetitie_id] = $repetitie;
			}
			return $result;
		}
		return $repetities;
	}

	public static function getRepetitie($mrid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new Exception('Get maaltijd-repetitie faalt: Invalid $mrid =' . $mrid);
		}
		$repetities = self::loadRepetities('mlt_repetitie_id = ?', array($mrid), 1);
		if (!array_key_exists(0, $repetities)) {
			throw new Exception('Get maaltijd-repetitie faalt: Not found $mrid =' . $mrid);
		}
		return $repetities[0];
	}

	/**
	 * @param string $where
	 * @param array $values
	 * @param int $limit
	 * @return MaaltijdRepetitie[]
	 */
	private static function loadRepetities($where = null, $values = array(), $limit = null) {
        return static::instance()->find($where, $values, null, 'periode_in_dagen ASC, dag_vd_week ASC', $limit)->fetchAll();
	}

	public static function saveRepetitie($mrid, $dag, $periode, $titel, $tijd, $prijs, $abo, $limiet, $filter) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			$abos = 0;
			if ($mrid === 0) {
				$repetitie = self::newRepetitie($dag, $periode, $titel, $tijd, $prijs, $abo, $limiet, $filter);
			} else {
				$repetitie = self::getRepetitie($mrid);
				$repetitie->dag_vd_week = $dag;
				$repetitie->periode_in_dagen = $periode;
				$repetitie->standaard_titel = $titel;
				$repetitie->standaard_tijd = $tijd;
				$repetitie->standaard_prijs = $prijs;
				$repetitie->abonneerbaar = (boolean) $abo;
				$repetitie->standaard_limiet = $limiet;
				$repetitie->abonnement_filter = $filter;
				self::updateRepetitie($repetitie);
				if (!$abo) { // niet (meer) abonneerbaar
					$abos = MaaltijdAbonnementenModel::verwijderAbonnementen($mrid);
				}
			}
			$db->commit();
			return array($repetitie, $abos);
		} catch (\Exception $e) {
			$db->rollBack();
			throw $e; // rethrow to controller
		}
	}

	private static function updateRepetitie(MaaltijdRepetitie $repetitie) {
		$sql = 'UPDATE mlt_repetities';
		$sql.= ' SET dag_vd_week=?, periode_in_dagen=?, standaard_titel=?, standaard_tijd=?, standaard_prijs=?, abonneerbaar=?, standaard_limiet=?, abonnement_filter=?';
		$sql.= ' WHERE mlt_repetitie_id=?';
		$values = array(
			$repetitie->dag_vd_week,
			$repetitie->periode_in_dagen,
			$repetitie->standaard_titel,
			$repetitie->standaard_tijd,
			$repetitie->standaard_prijs,
			werkomheen_pdo_bool($repetitie->abonneerbaar),
			$repetitie->standaard_limiet,
			$repetitie->abonnement_filter,
			$repetitie->mlt_repetitie_id
		);
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			//throw new Exception('Update maaltijd-repetitie faalt: $query->rowCount() ='. $query->rowCount());
		}
	}

	private static function newRepetitie($dag, $periode, $titel, $tijd, $prijs, $abo, $limiet, $filter) {
		$sql = 'INSERT INTO mlt_repetities';
		$sql.= ' (mlt_repetitie_id, dag_vd_week, periode_in_dagen, standaard_titel, standaard_tijd, standaard_prijs, abonneerbaar, standaard_limiet, abonnement_filter)';
		$sql.= ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$values = array(null, $dag, $periode, $titel, $tijd, $prijs, werkomheen_pdo_bool($abo), $limiet, $filter);
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new Exception('New maaltijd-repetitie faalt: $query->rowCount() =' . $query->rowCount());
		}
		return static::instance()->nieuwMaaltijdRepetitie(intval($db->lastInsertId()), $dag, $periode, $titel, $tijd, $prijs, $abo, $limiet, $filter);
	}

	public static function verwijderRepetitie($mrid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new Exception('Verwijder maaltijd-repetitie faalt: Invalid $mrid =' . $mrid);
		}
		if (CorveeRepetitiesModel::existMaaltijdRepetitieCorvee($mrid)) {
			throw new Exception('Ontkoppel of verwijder eerst de bijbehorende corvee-repetities!');
		}
		if (MaaltijdenModel::instance()->existRepetitieMaaltijden($mrid)) {
			MaaltijdenModel::verwijderRepetitieMaaltijden($mrid); // delete maaltijden first (foreign key)
			throw new Exception('Alle bijbehorende maaltijden zijn naar de prullenbak verplaatst. Verwijder die eerst!');
		}
		return self::deleteRepetitie($mrid);
	}

	private static function deleteRepetitie($mrid) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			$aantal = MaaltijdAbonnementenModel::verwijderAbonnementen($mrid); // delete abonnementen first (foreign key)
			$sql = 'DELETE FROM mlt_repetities';
			$sql.= ' WHERE mlt_repetitie_id=?';
			$values = array($mrid);
			$query = $db->prepare($sql);
			$query->execute($values);
			if ($query->rowCount() !== 1) {
				throw new Exception('Delete maaltijd-repetitie faalt: $query->rowCount() =' . $query->rowCount());
			}
			$db->commit();
			return $aantal;
		} catch (\Exception $e) {
			$db->rollBack();
			throw $e; // rethrow to controller
		}
	}

}
