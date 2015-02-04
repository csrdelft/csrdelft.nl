<?php

require_once 'model/entity/maalcie/CorveeTaak.class.php';
require_once 'model/maalcie/FunctiesModel.class.php';
require_once 'model/maalcie/CorveePuntenModel.class.php';

/**
 * CorveeTakenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class CorveeTakenModel {

	/**
	 * Do NOT use @ and . in your primary keys or you WILL run into trouble here!
	 * 
	 * @param string $UUID
	 * @return PersistentEntity
	 */
	public static function getUUID($UUID) {
		$parts = explode('@', $UUID, 2);
		$primary_key_values = explode('.', $parts[0]);
		return static::instance()->retrieveByPrimaryKey($primary_key_values);
	}

	public static function updateGemaild(CorveeTaak $taak) {
		$taak->setWanneerGemaild(date('Y-m-d H:i'));
		self::updateTaak($taak);
	}

	public static function taakToewijzenAanLid(CorveeTaak $taak, $uid) {
		if ($taak->getUid() === $uid) {
			return false;
		}
		$puntenruilen = false;
		if ($taak->getWanneerToegekend() !== null) {
			$puntenruilen = true;
		}
		$taak->setWanneerGemaild('');
		if ($puntenruilen && $taak->getUid() !== null) {
			self::puntenIntrekken($taak);
		}
		$taak->setUid($uid);
		if ($puntenruilen && $uid !== null) {
			self::puntenToekennen($taak);
		} else {
			self::updateTaak($taak);
		}
		return true;
	}

	public static function puntenToekennen(CorveeTaak $taak) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			CorveePuntenModel::puntenToekennen($taak->getUid(), $taak->getPunten(), $taak->getBonusMalus());
			$taak->setPuntenToegekend($taak->getPuntenToegekend() + $taak->getPunten());
			$taak->setBonusToegekend($taak->getBonusToegekend() + $taak->getBonusMalus());
			$taak->setWanneerToegekend(date('Y-m-d H:i'));
			self::updateTaak($taak);
			$db->commit();
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

	public static function puntenIntrekken(CorveeTaak $taak) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			CorveePuntenModel::puntenIntrekken($taak->getUid(), $taak->getPunten(), $taak->getBonusMalus());
			$taak->setPuntenToegekend($taak->getPuntenToegekend() - $taak->getPunten());
			$taak->setBonusToegekend($taak->getBonusToegekend() - $taak->getBonusMalus());
			$taak->setWanneerToegekend(null);
			self::updateTaak($taak);
			$db->commit();
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

	public static function getRoosterMatrix(array $taken) {
		$matrix = array();
		foreach ($taken as $taak) {
			$datum = strtotime($taak->getDatum());
			$week = date('W', $datum);
			$matrix[$week][$datum][$taak->getFunctieId()][] = $taak;
		}
		return $matrix;
	}

	public static function getKomendeTaken() {
		return self::loadTaken('verwijderd = FALSE AND datum >= ?', array(date('Y-m-d')));
	}

	public static function getVerledenTaken() {
		return self::loadTaken('verwijderd = FALSE AND datum < ?', array(date('Y-m-d')));
	}

	public static function getAlleTaken($groupByUid = false) {
		$taken = self::loadTaken('verwijderd = FALSE');
		if ($groupByUid) {
			$takenByUid = array();
			foreach ($taken as $taak) {
				$uid = $taak->getUid();
				if ($uid !== null) {
					$takenByUid[$uid][] = $taak;
				}
			}
			return $takenByUid;
		}
		return $taken;
	}

	public static function getVerwijderdeTaken() {
		return self::loadTaken('verwijderd = true');
	}

	public static function getTaak($tid) {
		$taak = self::loadTaak($tid);
		if ($taak->getIsVerwijderd()) {
			throw new Exception('Maaltijd is verwijderd');
		}
		return $taak;
	}

	private static function loadTaak($tid) {
		if (!is_int($tid) || $tid <= 0) {
			throw new Exception('Load taak faalt: Invalid $tid =' . $tid);
		}
		$taken = self::loadTaken('taak_id = ?', array($tid), 1);
		if (!array_key_exists(0, $taken)) {
			throw new Exception('Load taak faalt: Not found $tid =' . $tid);
		}
		return $taken[0];
	}

	/**
	 * Haalt de taken op voor het ingelode lid of alle leden tussen de opgegeven data.
	 * 
	 * @param timestamp $van
	 * @param timestamp $tot
	 * @return CorveeTaak[] (implements Agendeerbaar)
	 */
	public static function getTakenVoorAgenda($van, $tot, $iedereen = false) {
		if (!is_int($van)) {
			throw new Exception('Invalid timestamp: $van getTakenVoorAgenda()');
		}
		if (!is_int($tot)) {
			throw new Exception('Invalid timestamp: $tot getTakenVoorAgenda()');
		}
		$where = 'verwijderd = FALSE AND datum >= ? AND datum <= ?';
		$values = array(date('Y-m-d', $van), date('Y-m-d', $tot));
		if (!$iedereen) {
			$where .= ' AND uid = ?';
			$values[] = \LoginModel::getUid();
		}
		return self::loadTaken($where, $values);
	}

	/**
	 * Haalt de taken op voor een lid.
	 * 
	 * @param string $uid
	 * @return CorveeTaak[]
	 */
	public static function getTakenVoorLid($uid) {
		return self::loadTaken('verwijderd = FALSE AND uid = ?', array($uid));
	}

	/**
	 * Zoekt de laatste taak op van een lid.
	 * 
	 * @param string $uid
	 * @return CorveeTaak[]
	 */
	public static function getLaatsteTaakVanLid($uid) {
		$taken = self::loadTaken('verwijderd = FALSE AND uid = ?', array($uid), 1, false);
		if (!array_key_exists(0, $taken)) {
			return null;
		}
		return $taken[0];
	}

	/**
	 * Haalt de komende taken op waarvoor een lid is ingedeeld.
	 * 
	 * @param string $uid
	 * @return CorveeTaak[]
	 */
	public static function getKomendeTakenVoorLid($uid) {
		return self::loadTaken('verwijderd = FALSE AND uid = ? AND datum >= ?', array($uid, date('Y-m-d')));
	}

	public static function saveTaak($tid, $fid, $uid, $crid, $mid, $datum, $punten, $bonus_malus) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			if ($tid === 0) {
				$taak = self::newTaak($fid, $uid, $crid, $mid, $datum, $punten, $bonus_malus);
			} else {
				$taak = self::getTaak($tid);
				if ($taak->getFunctieId() !== $fid) {
					$taak->setCorveeRepetitieId(null);
					$taak->setFunctieId($fid);
				}
				$taak->setMaaltijdId($mid);
				$taak->setDatum($datum);
				$taak->setPunten($punten);
				$taak->setBonusMalus($bonus_malus);
				if (!self::taakToewijzenAanLid($taak, $uid)) {
					self::updateTaak($taak);
				}
			}
			$db->commit();
			return $taak;
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

	public static function herstelTaak($tid) {
		$taak = self::loadTaak($tid);
		if (!$taak->getIsVerwijderd()) {
			throw new Exception('Corveetaak is niet verwijderd');
		}
		$taak->setVerwijderd(false);
		self::updateTaak($taak);
		return $taak;
	}

	public static function prullenbakLeegmaken() {
		$sql = 'DELETE FROM crv_taken';
		$sql.= ' WHERE verwijderd = true';
		$values = array();
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		return $query->rowCount();
	}

	public static function verwijderOudeTaken() {
		$sql = 'UPDATE crv_taken';
		$sql.= ' SET verwijderd = true';
		$sql.= ' WHERE datum < ?';
		$values = array(date('Y-m-d'));
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		return $query->rowCount();
	}

	public static function verwijderTakenVoorLid($uid) {
		$sql = 'UPDATE crv_taken';
		$sql.= ' SET uid = ?';
		$sql.= ' WHERE uid = ? AND datum >= ?';
		$values = array(null, $uid, date('Y-m-d'));
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		return $query->rowCount();
	}

	public static function verwijderTaak($tid) {
		$taak = self::loadTaak($tid);
		if ($taak->getIsVerwijderd()) {
			self::deleteTaken($tid); // definitief verwijderen
		} else {
			$taak->setVerwijderd(true);
			self::updateTaak($taak);
		}
	}

	private static function deleteTaken($tid = null) {
		$sql = 'DELETE FROM crv_taken';
		$sql.= ' WHERE taak_id = ?';
		$values = array($tid);
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new Exception('Delete taak faalt: $query->rowCount() =' . $query->rowCount());
		}
	}

	private static function loadTaken($where = null, $values = array(), $limit = null, $orderAsc = true) {
		$sql = 'SELECT taak_id, functie_id, uid, crv_repetitie_id, maaltijd_id, datum, punten, bonus_malus, punten_toegekend, bonus_toegekend, wanneer_toegekend, wanneer_gemaild, verwijderd';
		$sql.= ' FROM crv_taken';
		if ($where !== null) {
			$sql.= ' WHERE ' . $where;
		}
		$sql.= ' ORDER BY datum ' . ($orderAsc ? 'ASC' : 'DESC') . ', functie_id ASC';
		if (is_int($limit) && $limit > 0) {
			$sql.= ' LIMIT ' . $limit;
		}
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, '\CorveeTaak');
		return $result;
	}

	private static function updateTaak(CorveeTaak $taak) {
		$sql = 'UPDATE crv_taken';
		$sql.= ' SET functie_id=?, uid=?, crv_repetitie_id=?, maaltijd_id=?, datum=?, punten=?, bonus_malus=?, punten_toegekend=?, bonus_toegekend=?, wanneer_toegekend=?, wanneer_gemaild=?, verwijderd=?';
		$sql.= ' WHERE taak_id=?';
		$values = array(
			$taak->getFunctieId(),
			$taak->getUid(),
			$taak->getCorveeRepetitieId(),
			$taak->getMaaltijdId(),
			$taak->getDatum(),
			$taak->getPunten(),
			$taak->getBonusMalus(),
			$taak->getPuntenToegekend(),
			$taak->getBonusToegekend(),
			$taak->getWanneerToegekend(),
			$taak->getWanneerGemaild(),
			werkomheen_pdo_bool($taak->getIsVerwijderd()),
			$taak->getTaakId()
		);
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new Exception('Update taak faalt: $query->rowCount() =' . $query->rowCount());
		}
	}

	private static function newTaak($fid, $uid, $crid, $mid, $datum, $punten, $bonus_malus) {
		if ($mid !== null && (!is_int($mid) || $mid < 1)) {
			throw new Exception('New taak faalt: $mid =' . $mid);
		}
		$sql = 'INSERT INTO crv_taken';
		$sql.= ' (taak_id, functie_id, uid, crv_repetitie_id, maaltijd_id, datum, punten, bonus_malus, punten_toegekend, bonus_toegekend, wanneer_toegekend, wanneer_gemaild, verwijderd)';
		$sql.= ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$values = array(null, $fid, $uid, $crid, $mid, $datum, $punten, $bonus_malus, 0, 0, null, '', werkomheen_pdo_bool(false));
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new Exception('New taak faalt: $query->rowCount() =' . $query->rowCount());
		}
		$taak = new CorveeTaak(intval($db->lastInsertId()), $fid, $uid, $crid, $mid, $datum, $punten, $bonus_malus, 0, 0, null, '', false);
		return $taak;
	}

	// Maaltijd-Corvee ############################################################

	/**
	 * Haalt de taken op die gekoppeld zijn aan een maaltijd.
	 * Eventueel ook alle verwijderde taken.
	 * 
	 * @param int $mid
	 * @return CorveeTaak[]
	 */
	public static function getTakenVoorMaaltijd($mid, $verwijderd = false) {
		if (!is_int($mid) || $mid <= 0) {
			throw new Exception('Load taken voor maaltijd faalt: Invalid $mid =' . $mid);
		}
		if ($verwijderd) {
			return self::loadTaken('maaltijd_id = ?', array($mid));
		}
		return self::loadTaken('verwijderd = FALSE AND maaltijd_id = ?', array($mid));
	}

	/**
	 * Called when a Maaltijd is going to be deleted.
	 * 
	 * @param int $mid
	 * @return boolean
	 */
	public static function existMaaltijdCorvee($mid) {
		if (!is_int($mid) || $mid <= 0) {
			throw new Exception('Exist maaltijd-corvee faalt: Invalid $mid =' . $mid);
		}
		$sql = 'SELECT EXISTS (SELECT * FROM crv_taken WHERE maaltijd_id = ?)';
		$values = array($mid);
		$query = \Database::instance()->prepare($sql);
		$query->execute($values);
		$result = $query->fetchColumn();
		return $result;
	}

	/**
	 * Called when a Maaltijd is going to be deleted.
	 * 
	 * @param int $mid
	 */
	public static function verwijderMaaltijdCorvee($mid) {
		if (!is_int($mid) || $mid <= 0) {
			throw new Exception('Delete maaltijd-corvee faalt: Invalid $mid =' . $mid);
		}
		$sql = 'UPDATE crv_taken SET verwijderd = true WHERE maaltijd_id = ?';
		$values = array($mid);
		$query = \Database::instance()->prepare($sql);
		$query->execute($values);
		return $query->rowCount();
	}

	// Functie-Taken ############################################################

	/**
	 * Haalt de taken op van een bepaalde functie.
	 * 
	 * @param int $fid
	 * @return CorveeTaak[]
	 */
	public static function getTakenVanFunctie($fid) {
		if (!is_int($fid) || $fid <= 0) {
			throw new Exception('Load taken van functie faalt: Invalid $fid =' . $fid);
		}
		return self::loadTaken('verwijderd = FALSE AND functie_id = ?', array($fid));
	}

	/**
	 * Called when a CorveeFunctie is going to be deleted.
	 * 
	 * @param int $fid
	 * @return boolean
	 */
	public static function existFunctieTaken($fid) {
		if (!is_int($fid) || $fid <= 0) {
			throw new Exception('Exist functie-taken faalt: Invalid $fid =' . $fid);
		}
		$sql = 'SELECT EXISTS (SELECT * FROM crv_taken WHERE functie_id = ?)';
		$values = array($fid);
		$query = \Database::instance()->prepare($sql);
		$query->execute($values);
		$result = $query->fetchColumn();
		return $result;
	}

	// Repetitie-Taken ############################################################

	public static function maakRepetitieTaken(CorveeRepetitie $repetitie, $beginDatum, $eindDatum, $mid = null) {
		if ($repetitie->getPeriodeInDagen() < 1) {
			throw new Exception('New repetitie-taken faalt: $periode =' . $repetitie->getPeriodeInDagen());
		}
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			$taken = self::newRepetitieTaken($repetitie, strtotime($beginDatum), strtotime($eindDatum), $mid);
			$db->commit();
			return $taken;
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

	public static function newRepetitieTaken(CorveeRepetitie $repetitie, $beginDatum, $eindDatum, $mid = null) {
		// start at first occurence
		$shift = $repetitie->getDagVanDeWeek() - date('w', $beginDatum) + 7;
		$shift %= 7;
		if ($shift > 0) {
			$beginDatum = strtotime('+' . $shift . ' days', $beginDatum);
		}
		$datum = $beginDatum;
		$taken = array();
		while ($datum <= $eindDatum) { // break after one
			for ($i = $repetitie->getStandaardAantal(); $i > 0; $i--) {
				$taak = self::newTaak($repetitie->getFunctieId(), null, $repetitie->getCorveeRepetitieId(), $mid, date('Y-m-d', $datum), $repetitie->getStandaardPunten(), 0);
				$taken[] = $taak;
			}
			if ($repetitie->getPeriodeInDagen() < 1) {
				break;
			}
			$datum = strtotime('+' . $repetitie->getPeriodeInDagen() . ' days', $datum);
		}
		return $taken;
	}

	public static function verwijderRepetitieTaken($crid) {
		if (!is_int($crid) || $crid <= 0) {
			throw new Exception('Verwijder repetitie-taken faalt: Invalid $crid =' . $crid);
		}
		$sql = 'UPDATE crv_taken';
		$sql.= ' SET verwijderd = true';
		$sql.= ' WHERE crv_repetitie_id = ?';
		$values = array($crid);
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		return $query->rowCount();
	}

	/**
	 * Called when a CorveeRepetitie is updated or is going to be deleted.
	 * 
	 * @param int $crid
	 * @return boolean
	 */
	public static function existRepetitieTaken($crid) {
		if (!is_int($crid) || $crid <= 0) {
			throw new Exception('Exist repetitie-taken faalt: Invalid $crid =' . $crid);
		}
		$sql = 'SELECT EXISTS (SELECT * FROM crv_taken WHERE crv_repetitie_id = ?)';
		$values = array($crid);
		$query = \Database::instance()->prepare($sql);
		$query->execute($values);
		$result = (boolean) $query->fetchColumn();
		return $result;
	}

	public static function updateRepetitieTaken(CorveeRepetitie $repetitie, $verplaats) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			$sql = 'UPDATE crv_taken';
			$sql.= ' SET functie_id=?, punten=?';
			$sql.= ' WHERE verwijderd = FALSE AND crv_repetitie_id = ?';
			$values = array(
				$repetitie->getFunctieId(),
				$repetitie->getStandaardPunten(),
				$repetitie->getCorveeRepetitieId()
			);
			$query = $db->prepare($sql);
			$query->execute($values);
			$updatecount = $query->rowCount();

			$taken = self::loadTaken('verwijderd = FALSE AND crv_repetitie_id = ?', array($repetitie->getCorveeRepetitieId()));
			$takenPerDatum = array(); // taken per datum indien geen maaltijd
			$takenPerMaaltijd = array(); // taken per maaltijd
			require_once 'model/maalcie/MaaltijdenModel.class.php';
			$maaltijden = MaaltijdenModel::getKomendeRepetitieMaaltijden($repetitie->getMaaltijdRepetitieId(), true);
			$maaltijdenById = array();
			foreach ($maaltijden as $maaltijd) {
				$takenPerMaaltijd[$maaltijd->getMaaltijdId()] = array();
				$maaltijdenById[$maaltijd->getMaaltijdId()] = $maaltijd;
			}
			// update day of the week
			$daycount = 0;
			foreach ($taken as $taak) {
				$datum = strtotime($taak->getDatum());
				if ($verplaats) {
					$shift = $repetitie->getDagVanDeWeek() - date('w', $datum);
					if ($shift > 0) {
						$datum = strtotime('+' . $shift . ' days', $datum);
					} elseif ($shift < 0) {
						$datum = strtotime($shift . ' days', $datum);
					}
					if ($shift !== 0) {
						$taak->setDatum(date('Y-m-d', $datum));
						self::updateTaak($taak);
						$daycount++;
					}
				}
				$mid = $taak->getMaaltijdId();
				if ($mid !== null) {
					if (array_key_exists($mid, $maaltijdenById)) { // do not change if not komende repetitie maaltijd
						$takenPerMaaltijd[$mid][] = $taak;
					}
				} else {
					$takenPerDatum[$datum][] = $taak;
				}
			}
			// standaard aantal aanvullen
			$datumcount = 0;
			foreach ($takenPerDatum as $datum => $taken) {
				$verschil = $repetitie->getStandaardAantal() - sizeof($taken);
				for ($i = $verschil; $i > 0; $i--) {
					self::newTaak(
							$repetitie->getFunctieId(), null, $repetitie->getCorveeRepetitieId(), null, $taken[0]->getDatum(), $repetitie->getStandaardPunten(), 0
					);
				}
				$datumcount += $verschil;
			}
			$maaltijdcount = 0;
			foreach ($takenPerMaaltijd as $mid => $taken) {
				$verschil = $repetitie->getStandaardAantal() - sizeof($taken);
				for ($i = $verschil; $i > 0; $i--) {
					self::newTaak(
							$repetitie->getFunctieId(), null, $repetitie->getCorveeRepetitieId(), $mid, $maaltijdenById[$mid]->getDatum(), $repetitie->getStandaardPunten(), 0
					);
				}
				$maaltijdcount += $verschil;
			}
			$db->commit();
			return array('update' => $updatecount, 'day' => $daycount, 'datum' => $datumcount, 'maaltijd' => $maaltijdcount);
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

}

?>