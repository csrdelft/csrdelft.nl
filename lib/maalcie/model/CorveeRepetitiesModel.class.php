<?php

require_once 'maalcie/model/entity/CorveeRepetitie.class.php';
require_once 'maalcie/model/CorveeTakenModel.class.php';
require_once 'maalcie/model/CorveeVoorkeurenModel.class.php';

/**
 * CorveeRepetitiesModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class CorveeRepetitiesModel {

	public static function getFirstOccurrence(CorveeRepetitie $repetitie) {
		$datum = time();
		$shift = $repetitie->getDagVanDeWeek() - date('w', $datum) + 7;
		$shift %= 7;
		if ($shift > 0) {
			$datum = strtotime('+' . $shift . ' days', $datum);
		}
		return date('Y-m-d', $datum);
	}

	public static function getVoorkeurbareRepetities($groupById = false) {
		$repetities = self::loadRepetities('voorkeurbaar = true');
		if ($groupById) {
			$result = array();
			foreach ($repetities as $repetitie) {
				$result[$repetitie->getCorveeRepetitieId()] = $repetitie;
			}
			return $result;
		}
		return $repetities;
	}

	public static function getAlleRepetities() {
		return self::loadRepetities();
	}

	/**
	 * Haalt de periodieke taken op die gekoppeld zijn aan een periodieke maaltijd.
	 * 
	 * @param int $mrid
	 * @return CorveeTaak[]
	 */
	public static function getRepetitiesVoorMaaltijdRepetitie($mrid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new Exception('Load taken voor maaltijd faalt: Invalid $mrid =' . $mrid);
		}
		return self::loadRepetities('mlt_repetitie_id = ?', array($mrid));
	}

	public static function getRepetitie($crid) {
		if (!is_int($crid) || $crid <= 0) {
			throw new Exception('Get corvee-repetitie faalt: Invalid $crid =' . $crid);
		}
		$repetities = self::loadRepetities('crv_repetitie_id = ?', array($crid), 1);
		if (!array_key_exists(0, $repetities)) {
			throw new Exception('Get corvee-repetitie faalt: Not found $crid =' . $crid);
		}
		return $repetities[0];
	}

	private static function loadRepetities($where = null, $values = array(), $limit = null) {
		$sql = 'SELECT crv_repetitie_id, mlt_repetitie_id, dag_vd_week, periode_in_dagen, functie_id, standaard_punten, standaard_aantal, voorkeurbaar';
		$sql.= ' FROM crv_repetities';
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
		$result = $query->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, '\CorveeRepetitie');
		// load corvee functies
		if ($query->rowCount() === 1) {
			$result[0]->setCorveeFunctie(FunctiesModel::instance()->getFunctie($result[0]->getFunctieId()));
		} elseif ($query->rowCount() > 1) {
			$functies = FunctiesModel::instance()->getAlleFuncties(); // grouped by functie_id
			foreach ($result as $repetitie) {
				$repetitie->setCorveeFunctie($functies[$repetitie->getFunctieId()]);
			}
		}
		return $result;
	}

	public static function saveRepetitie($crid, $mrid, $dag, $periode, $fid, $punten, $aantal, $voorkeur) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			$voorkeuren = 0;
			if ($crid === 0) {
				$repetitie = self::newRepetitie($mrid, $dag, $periode, $fid, $punten, $aantal, $voorkeur);
			} else {
				$repetitie = self::getRepetitie($crid);
				$repetitie->setMaaltijdRepetitieId($mrid);
				$repetitie->setDagVanDeWeek($dag);
				$repetitie->setPeriodeInDagen($periode);
				$repetitie->setFunctieId($fid);
				$repetitie->setStandaardPunten($punten);
				$repetitie->setStandaardAantal($aantal);
				$repetitie->setVoorkeurbaar($voorkeur);
				self::updateRepetitie($repetitie);
				if (!$voorkeur) { // niet (meer) voorkeurbaar
					$voorkeuren = CorveeVoorkeurenModel::verwijderVoorkeuren($crid);
				}
			}
			$repetitie->setCorveeFunctie(FunctiesModel::instance()->getFunctie($repetitie->getFunctieId()));
			$db->commit();
			return array($repetitie, $voorkeuren);
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

	private static function updateRepetitie(CorveeRepetitie $repetitie) {
		$sql = 'UPDATE crv_repetities';
		$sql.= ' SET mlt_repetitie_id=?, dag_vd_week=?, periode_in_dagen=?, functie_id=?, standaard_punten=?, standaard_aantal=?, voorkeurbaar=?';
		$sql.= ' WHERE crv_repetitie_id=?';
		$values = array(
			$repetitie->getMaaltijdRepetitieId(),
			$repetitie->getDagVanDeWeek(),
			$repetitie->getPeriodeInDagen(),
			$repetitie->getFunctieId(),
			$repetitie->getStandaardPunten(),
			$repetitie->getStandaardAantal(),
			werkomheen_pdo_bool($repetitie->getIsVoorkeurbaar()),
			$repetitie->getCorveeRepetitieId()
		);
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			//throw new Exception('Update corvee-repetitie faalt: $query->rowCount() ='. $query->rowCount());
		}
	}

	private static function newRepetitie($mrid, $dag, $periode, $fid, $punten, $aantal, $voorkeur) {
		$sql = 'INSERT INTO crv_repetities';
		$sql.= ' (crv_repetitie_id, mlt_repetitie_id, dag_vd_week, periode_in_dagen, functie_id, standaard_punten, standaard_aantal, voorkeurbaar)';
		$sql.= ' VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
		$values = array(null, $mrid, $dag, $periode, $fid, $punten, $aantal, werkomheen_pdo_bool($voorkeur));
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new Exception('New corvee-repetitie faalt: $query->rowCount() =' . $query->rowCount());
		}
		return new CorveeRepetitie(intval($db->lastInsertId()), $mrid, $dag, $periode, $fid, $punten, $aantal, $voorkeur);
	}

	public static function verwijderRepetitie($crid) {
		if (!is_int($crid) || $crid <= 0) {
			throw new Exception('Verwijder corvee-repetitie faalt: Invalid $crid =' . $crid);
		}
		if (CorveeTakenModel::existRepetitieTaken($crid)) {
			CorveeTakenModel::verwijderRepetitieTaken($crid); // delete corveetaken first (foreign key)
			throw new Exception('Alle bijbehorende corveetaken zijn naar de prullenbak verplaatst. Verwijder die eerst!');
		}
		return self::deleteRepetitie($crid);
	}

	private static function deleteRepetitie($crid) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			$aantal = CorveeVoorkeurenModel::verwijderVoorkeuren($crid); // delete voorkeuren first (foreign key)
			$sql = 'DELETE FROM crv_repetities';
			$sql.= ' WHERE crv_repetitie_id=?';
			$values = array($crid);
			$query = $db->prepare($sql);
			$query->execute($values);
			if ($query->rowCount() !== 1) {
				throw new Exception('Delete corvee-repetitie faalt: $query->rowCount() =' . $query->rowCount());
			}
			$db->commit();
			return $aantal;
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

	// Maaltijd-Repetitie-Corvee ############################################################

	/**
	 * Called when a MaaltijdRepetitie is going to be deleted.
	 * 
	 * @param int $mrid
	 * @return boolean
	 */
	public static function existMaaltijdRepetitieCorvee($mrid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new Exception('Exist maaltijd-repetitie-corvee faalt: Invalid $mid =' . $mrid);
		}
		$sql = 'SELECT EXISTS (SELECT * FROM crv_repetities WHERE mlt_repetitie_id = ?)';
		$values = array($mrid);
		$query = \Database::instance()->prepare($sql);
		$query->execute($values);
		$result = $query->fetchColumn();
		return $result;
	}

	// Functie-Repetities ############################################################

	/**
	 * Called when a CorveeFunctie is going to be deleted.
	 * 
	 * @param int $fid
	 * @return boolean
	 */
	public static function existFunctieRepetities($fid) {
		if (!is_int($fid) || $fid <= 0) {
			throw new Exception('Exist functie-repetities faalt: Invalid $fid =' . $fid);
		}
		$sql = 'SELECT EXISTS (SELECT * FROM crv_repetities WHERE functie_id = ?)';
		$values = array($fid);
		$query = \Database::instance()->prepare($sql);
		$query->execute($values);
		$result = $query->fetchColumn();
		return $result;
	}

}

?>