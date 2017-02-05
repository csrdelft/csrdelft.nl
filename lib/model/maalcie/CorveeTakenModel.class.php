<?php

require_once 'model/entity/maalcie/CorveeTaak.class.php';
require_once 'model/maalcie/FunctiesModel.class.php';
require_once 'model/maalcie/CorveePuntenModel.class.php';

/**
 * CorveeTakenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class CorveeTakenModel extends PersistenceModel {
	const ORM = 'CorveeTaak';
	const DIR = 'maalcie/';

	protected static $instance;

	public static function updateGemaild(CorveeTaak $taak) {
		$taak->setWanneerGemaild(date('Y-m-d H:i'));
		static::instance()->update($taak);
	}

	public static function taakToewijzenAanLid(CorveeTaak $taak, $uid) {
		if ($taak->uid === $uid) {
			return false;
		}
		$puntenruilen = false;
		if ($taak->wanneer_toegekend !== null) {
			$puntenruilen = true;
		}
		$taak->wanneer_gemaild = '';
		if ($puntenruilen && $taak->uid !== null) {
			self::puntenIntrekken($taak);
		}
		$taak->setUid($uid);
		if ($puntenruilen && $uid !== null) {
			self::puntenToekennen($taak);
		} else {
			static::instance()->update($taak);
		}
		return true;
	}

	public static function puntenToekennen(CorveeTaak $taak) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			CorveePuntenModel::puntenToekennen($taak->uid, $taak->punten, $taak->bonus_malus);
			$taak->punten_toegekend = $taak->punten_toegekend + $taak->punten;
			$taak->bonus_toegekend = $taak->bonus_toegekend + $taak->bonus_malus;
			$taak->wanneer_toegekend = date('Y-m-d H:i');
			static::instance()->update($taak);
			$db->commit();
		} catch (\Exception $e) {
			$db->rollBack();
			throw $e; // rethrow to controller
		}
	}

	public static function puntenIntrekken(CorveeTaak $taak) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			CorveePuntenModel::puntenIntrekken($taak->uid, $taak->punten, $taak->bonus_malus);
			$taak->punten_toegekend = $taak->punten_toegekend - $taak->punten;
			$taak->bonus_toegekend = $taak->bonus_toegekend - $taak->bonus_malus;
			$taak->wanneer_toegekend = null;
			static::instance()->update($taak);
			$db->commit();
		} catch (\Exception $e) {
			$db->rollBack();
			throw $e; // rethrow to controller
		}
	}

	public static function getRoosterMatrix(array $taken) {
		$matrix = array();
		foreach ($taken as $taak) {
			$datum = strtotime($taak->datum);
			$week = date('W', $datum);
			$matrix[$week][$datum][$taak->functie_id][] = $taak;
		}
		return $matrix;
	}

	public static function getKomendeTaken() {
		return static::instance()->find('verwijderd = false AND datum >= ?', array(date('Y-m-d')));
	}

	public static function getVerledenTaken() {
		return static::instance()->find('verwijderd = false AND datum < ?', array(date('Y-m-d')));
	}

	public static function getAlleTaken($groupByUid = false) {
		$taken = static::instance()->find('verwijderd = false');
		if ($groupByUid) {
			$takenByUid = array();
			foreach ($taken as $taak) {
				$uid = $taak->uid;
				if ($uid !== null) {
					$takenByUid[$uid][] = $taak;
				}
			}
			return $takenByUid;
		}
		return $taken;
	}

	public static function getVerwijderdeTaken() {
		return static::instance()->find('verwijderd = true');

	}

	public static function getTaak($tid) {
		$taak = static::instance()->retrieveByPrimaryKey(array($tid)); /** @var CorveeTaak $taak */
		if ($taak->verwijderd) {
			throw new Exception('Maaltijd is verwijderd');
		}
		return $taak;
	}

	/**
	 * Haalt de taken op voor het ingelode lid of alle leden tussen de opgegeven data.
	 *
	 * @param int $van Timestamp
	 * @param int $tot Timestamp
	 * @param bool $iedereen
	 * @return PDOStatement|CorveeTaak[]
	 * @throws Exception
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
			$values[] = LoginModel::getUid();
		}
		return static::instance()->find($where, $values);
	}

	/**
	 * Haalt de taken op voor een lid.
	 * 
	 * @param string $uid
	 * @return PDOStatement
	 */
	public static function getTakenVoorLid($uid) {
		return static::instance()->find('verwijderd = false AND uid = ?', array($uid));
	}

	/**
	 * Zoekt de laatste taak op van een lid.
	 * 
	 * @param string $uid
	 * @return CorveeTaak
	 */
	public static function getLaatsteTaakVanLid($uid) {
		return static::instance()->find('verwijderd = false AND uid = ?', array($uid), null, null, 1)->fetch();
	}

	/**
	 * Haalt de komende taken op waarvoor een lid is ingedeeld.
	 * 
	 * @param string $uid
	 * @return PDOStatement|CorveeTaak[]
	 */
	public static function getKomendeTakenVoorLid($uid) {
		return static::instance()->find('verwijderd = false AND uid = ? AND datum >= ?', array($uid, date('Y-m-d')));
	}

	public static function saveTaak($tid, $fid, $uid, $crid, $mid, $datum, $punten, $bonus_malus) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			if ($tid === 0) {
				$taak = self::newTaak($fid, $uid, $crid, $mid, $datum, $punten, $bonus_malus);
			} else {
				$taak = self::getTaak($tid);
				if ($taak->functie_id !== $fid) {
					$taak->crv_repetitie_id = null;
					$taak->functie_id = $fid;
				}
				$taak->maaltijd_id = $mid;
				$taak->datum = $datum;
				$taak->punten = $punten;
				$taak->bonus_malus = $bonus_malus;
				if (!self::taakToewijzenAanLid($taak, $uid)) {
					static::instance()->update($taak);
				}
			}
			$db->commit();
			return $taak;
		} catch (\Exception $e) {
			$db->rollBack();
			throw $e; // rethrow to controller
		}
	}

	public static function herstelTaak($tid) {
		$taak = static::instance()->retrieveByPrimaryKey(array($tid)); /** @var CorveeTaak $taak */
		if (!$taak->verwijderd) {
			throw new Exception('Corveetaak is niet verwijderd');
		}
		$taak->verwijderd = false;
		static::instance()->update($taak);
		return $taak;
	}

	public static function prullenbakLeegmaken() {
		$taken = static::instance()->find('verwijderd = true');
		foreach ($taken as $taak) {
			static::instance()->delete($taak);
		}
		return $taken->rowCount();
	}

	public static function verwijderOudeTaken() {
		$taken = static::instance()->find('datum < ?', array(date('Y-m-d')));
		foreach ($taken as $taak) {
			$taak->verwijderd = true;
			static::instance()->update($taak);
		}
		return $taken->rowCount();
	}

	public static function verwijderTakenVoorLid($uid) {
		$taken = static::instance()->find('uid = ? AND datum >= ?', array($uid, date('Y-m-d')));
		foreach ($taken as $taak) {
			static::instance()->delete($taak);
		}
		return $taken->rowCount();
	}

	public static function verwijderTaak($tid) {
		$taak = static::instance()->retrieveByPrimaryKey(array($tid)); /** @var CorveeTaak $taak */
		if ($taak->verwijderd) {
			static::instance()->delete($taak); // definitief verwijderen
		} else {
			$taak->verwijderd = true;
			static::instance()->update($taak);
		}
	}

	/**
	 * @param null $where
	 * @param array $values
	 * @param null $limit
	 * @param bool $orderAsc
	 * @return CorveeTaak[]
	 */
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
		$taak = new CorveeTaak();
		$taak->taak_id = (int) intval($db->lastInsertId());
		$taak->functie_id = $fid;
		$taak->setUid($uid);
		$taak->crv_repetitie_id = $crid;
		$taak->maaltijd_id = $mid;
		$taak->datum = $datum;
		$taak->punten = $punten;
		$taak->bonus_malus = $bonus_malus;
		$taak->punten_toegekend = 0;
		$taak->bonus_toegekend = 0;
		$taak->wanneer_toegekend = null;
		$taak->setWanneerGemaild('');
		$taak->verwijderd = false;
		return $taak;
	}

	// Maaltijd-Corvee ############################################################

	/**
	 * Haalt de taken op die gekoppeld zijn aan een maaltijd.
	 * Eventueel ook alle verwijderde taken.
	 *
	 * @param int $mid
	 * @param bool $verwijderd
	 * @return PDOStatement|CorveeTaak[]
	 * @throws Exception
	 */
	public static function getTakenVoorMaaltijd($mid, $verwijderd = false) {
		if (!is_int($mid) || $mid <= 0) {
			throw new Exception('Load taken voor maaltijd faalt: Invalid $mid =' . $mid);
		}
		if ($verwijderd) {
			return static::instance()->find('maaltijd_id = ?', array($mid));
		}
		return static::instance()->find('verwijderd = false AND maaltijd_id = ?', array($mid));
	}

	/**
	 * Called when a Maaltijd is going to be deleted.
	 *
	 * @param int $mid
	 * @return bool
	 * @throws Exception
	 */
	public static function existMaaltijdCorvee($mid) {
		return static::instance()->count('maaltijd_id = ?', array($mid)) > 0;
	}

	/**
	 * Called when a Maaltijd is going to be deleted.
	 *
	 * @param int $mid
	 * @return int
	 */
	public static function verwijderMaaltijdCorvee($mid) {
		$taken = static::instance()->find('maaltijd_id = ?', array($mid));
		foreach ($taken as $taak) {
			$taak->verwijderd = true;
			static::instance()->update($taak);
		}
		return $taken->rowCount();
	}

	// Functie-Taken ############################################################

	/**
	 * Haalt de taken op van een bepaalde functie.
	 *
	 * @param int $fid
	 * @return PDOStatement|CorveeTaak[]
	 * @throws Exception
	 */
	public static function getTakenVanFunctie($fid) {
		if (!is_int($fid) || $fid <= 0) {
			throw new Exception('Load taken van functie faalt: Invalid $fid =' . $fid);
		}
		return static::instance()->find('verwijderd = false AND functie_id = ?', array($fid));
	}

	/**
	 * Called when a CorveeFunctie is going to be deleted.
	 *
	 * @param int $fid
	 * @return bool
	 * @throws Exception
	 */
	public static function existFunctieTaken($fid) {
		return static::instance()->count('functie_id = ?', array($fid)) > 0;
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
			$db->rollBack();
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
		$taken = static::instance()->find('crv_repetitie_id = ?', array($crid));
		foreach ($taken as $taak) {
			$taak->verwijderd = true;
			static::instance()->update($taak);
		}

		return $taken->rowCount();
	}

	/**
	 * Called when a CorveeRepetitie is updated or is going to be deleted.
	 *
	 * @param int $crid
	 * @return bool
	 * @throws Exception
	 */
	public static function existRepetitieTaken($crid) {
		return static::instance()->count('crv_repetitie_id = ?', array($crid)) > 0;
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

			$taken = static::instance()->find('verwijderd = FALSE AND crv_repetitie_id = ?', array($repetitie->getCorveeRepetitieId()));
			$takenPerDatum = array(); // taken per datum indien geen maaltijd
			$takenPerMaaltijd = array(); // taken per maaltijd
			require_once 'model/maalcie/MaaltijdenModel.class.php';
			$maaltijden = MaaltijdenModel::instance()->getKomendeRepetitieMaaltijden($repetitie->getMaaltijdRepetitieId());
			$maaltijdenById = array();
			foreach ($maaltijden as $maaltijd) {
				$takenPerMaaltijd[$maaltijd->maaltijd_id] = array();
				$maaltijdenById[$maaltijd->maaltijd_id] = $maaltijd;
			}
			// update day of the week
			$daycount = 0;
			foreach ($taken as $taak) {
				$datum = strtotime($taak->datum);
				if ($verplaats) {
					$shift = $repetitie->getDagVanDeWeek() - date('w', $datum);
					if ($shift > 0) {
						$datum = strtotime('+' . $shift . ' days', $datum);
					} elseif ($shift < 0) {
						$datum = strtotime($shift . ' days', $datum);
					}
					if ($shift !== 0) {
						$taak->datum = date('Y-m-d', $datum);
						static::instance()->update($taak);
						$daycount++;
					}
				}
				$mid = $taak->maaltijd_id;
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
							$repetitie->getFunctieId(), null, $repetitie->getCorveeRepetitieId(), null, $taken[0]->datum, $repetitie->getStandaardPunten(), 0
					);
				}
				$datumcount += $verschil;
			}
			$maaltijdcount = 0;
			foreach ($takenPerMaaltijd as $mid => $taken) {
				$verschil = $repetitie->getStandaardAantal() - sizeof($taken);
				for ($i = $verschil; $i > 0; $i--) {
					self::newTaak(
							$repetitie->getFunctieId(), null, $repetitie->getCorveeRepetitieId(), $mid, $maaltijdenById[$mid]->datum, $repetitie->getStandaardPunten(), 0
					);
				}
				$maaltijdcount += $verschil;
			}
			$db->commit();
			return array('update' => $updatecount, 'day' => $daycount, 'datum' => $datumcount, 'maaltijd' => $maaltijdcount);
		} catch (\Exception $e) {
			$db->rollBack();
			throw $e; // rethrow to controller
		}
	}

}

?>