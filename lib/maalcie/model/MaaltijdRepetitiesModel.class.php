<?php

require_once 'maalcie/model/entity/MaaltijdRepetitie.class.php';
require_once 'maalcie/model/MaaltijdAbonnementenModel.class.php';
require_once 'maalcie/model/CorveeRepetitiesModel.class.php';

/**
 * MaaltijdRepetitiesModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MaaltijdRepetitiesModel {

	public static function getFirstOccurrence(MaaltijdRepetitie $repetitie) {
		$datum = time();
		$shift = $repetitie->getDagVanDeWeek() - date('w', $datum) + 7;
		$shift %= 7;
		if ($shift > 0) {
			$datum = strtotime('+' . $shift . ' days', $datum);
		}
		return date('Y-m-d', $datum);
	}

	/**
	 * Filtert de repetities met het abonnement-filter van de maaltijd-repetitie op de permissies van het ingelogde lid.
	 *
	 * @param MaaltijdRepetitie[] $repetities
	 * @param string $uid
	 * @return MaaltijdRepetitie[]
	 */
	public static function getAbonneerbareRepetitiesVoorLid($uid) {
		$repetities = self::loadRepetities('abonneerbaar = true');
		$result = array();
		foreach ($repetities as $repetitie) {
			if (MaaltijdAanmeldingenModel::checkAanmeldFilter($uid, $repetitie->getAbonnementFilter())) {
				$result[$repetitie->getMaaltijdRepetitieId()] = $repetitie;
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
				$result[$repetitie->getMaaltijdRepetitieId()] = $repetitie;
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

	private static function loadRepetities($where = null, $values = array(), $limit = null) {
		$sql = 'SELECT mlt_repetitie_id, dag_vd_week, periode_in_dagen, standaard_titel, standaard_tijd, standaard_prijs, abonneerbaar, standaard_limiet, abonnement_filter';
		$sql.= ' FROM mlt_repetities';
		if ($where !== null) {
			$sql.= ' WHERE ' . $where;
		}
		$sql.= ' ORDER BY periode_in_dagen ASC, dag_vd_week ASC';
		if (is_int($limit)) {
			$sql.= ' LIMIT ' . $limit;
		}
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, 'MaaltijdRepetitie');
		return $result;
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
				$repetitie->setDagVanDeWeek($dag);
				$repetitie->setPeriodeInDagen($periode);
				$repetitie->setStandaardTitel($titel);
				$repetitie->setStandaardTijd($tijd);
				$repetitie->setStandaardPrijs($prijs);
				$repetitie->setAbonneerbaar($abo);
				$repetitie->setStandaardLimiet($limiet);
				$repetitie->setAbonnementFilter($filter);
				self::updateRepetitie($repetitie);
				if (!$abo) { // niet (meer) abonneerbaar
					$abos = MaaltijdAbonnementenModel::verwijderAbonnementen($mrid);
				}
			}
			$db->commit();
			return array($repetitie, $abos);
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

	private static function updateRepetitie(MaaltijdRepetitie $repetitie) {
		$sql = 'UPDATE mlt_repetities';
		$sql.= ' SET dag_vd_week=?, periode_in_dagen=?, standaard_titel=?, standaard_tijd=?, standaard_prijs=?, abonneerbaar=?, standaard_limiet=?, abonnement_filter=?';
		$sql.= ' WHERE mlt_repetitie_id=?';
		$values = array(
			$repetitie->getDagVanDeWeek(),
			$repetitie->getPeriodeInDagen(),
			$repetitie->getStandaardTitel(),
			$repetitie->getStandaardTijd(),
			$repetitie->getStandaardPrijs(),
			$repetitie->getIsAbonneerbaar(),
			$repetitie->getStandaardLimiet(),
			$repetitie->getAbonnementFilter(),
			$repetitie->getMaaltijdRepetitieId()
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
		$values = array(null, $dag, $periode, $titel, $tijd, $prijs, $abo, $limiet, $filter);
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new Exception('New maaltijd-repetitie faalt: $query->rowCount() =' . $query->rowCount());
		}
		return new MaaltijdRepetitie(intval($db->lastInsertId()), $dag, $periode, $titel, $tijd, $prijs, $abo, $limiet, $filter);
	}

	public static function verwijderRepetitie($mrid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new Exception('Verwijder maaltijd-repetitie faalt: Invalid $mrid =' . $mrid);
		}
		if (CorveeRepetitiesModel::existMaaltijdRepetitieCorvee($mrid)) {
			throw new Exception('Ontkoppel of verwijder eerst de bijbehorende corvee-repetities!');
		}
		if (MaaltijdenModel::existRepetitieMaaltijden($mrid)) {
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
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

}

?>