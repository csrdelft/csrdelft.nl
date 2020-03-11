<?php

namespace CsrDelft\model\maalcie;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\maalcie\CorveeRepetitie;
use CsrDelft\model\entity\maalcie\CorveeTaak;
use CsrDelft\model\security\LoginModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;
use PDOStatement;

/**
 * CorveeTakenModel.class.php    |    P.W.G. Brussee (brussee@live.nl)
 *
 */
class CorveeTakenModel extends PersistenceModel {
	const ORM = CorveeTaak::class;

	protected $default_order = 'datum ASC';

	public function updateGemaild(CorveeTaak $taak) {
		$taak->setWanneerGemaild(date('Y-m-d H:i'));
		$this->update($taak);
	}

	public function taakToewijzenAanLid(CorveeTaak $taak, $uid) {
		if ($taak->uid === $uid) {
			return false;
		}
		$puntenruilen = false;
		if ($taak->wanneer_toegekend !== null) {
			$puntenruilen = true;
		}
		$taak->wanneer_gemaild = '';
		if ($puntenruilen && $taak->uid !== null) {
			$this->puntenIntrekken($taak);
		}
		$taak->setUid($uid);
		if ($puntenruilen && $uid !== null) {
			$this->puntenToekennen($taak);
		} else {
			$this->update($taak);
		}
		return true;
	}

	public function puntenToekennen(CorveeTaak $taak) {
		CorveePuntenModel::puntenToekennen($taak->uid, $taak->punten, $taak->bonus_malus);
		$taak->punten_toegekend = $taak->punten_toegekend + $taak->punten;
		$taak->bonus_toegekend = $taak->bonus_toegekend + $taak->bonus_malus;
		$taak->wanneer_toegekend = date('Y-m-d H:i');
		$this->update($taak);
	}

	public function puntenIntrekken(CorveeTaak $taak) {
		CorveePuntenModel::puntenIntrekken($taak->uid, $taak->punten, $taak->bonus_malus);
		$taak->punten_toegekend = $taak->punten_toegekend - $taak->punten;
		$taak->bonus_toegekend = $taak->bonus_toegekend - $taak->bonus_malus;
		$taak->wanneer_toegekend = null;
		$this->update($taak);
	}

	public function getRoosterMatrix(array $taken) {
		$matrix = array();
		foreach ($taken as $taak) {
			$datum = strtotime($taak->datum);
			$week = date('W', $datum);
			$matrix[$week][$datum][$taak->functie_id][] = $taak;
		}
		return $matrix;
	}

	public function getKomendeTaken() {
		return $this->find('verwijderd = false AND datum >= ?', array(date('Y-m-d')));
	}

	public function getVerledenTaken() {
		return $this->find('verwijderd = false AND datum < ?', array(date('Y-m-d')));
	}

	public function getAlleTaken($groupByUid = false) {
		$taken = $this->find('verwijderd = false');
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

	public function getVerwijderdeTaken() {
		return $this->find('verwijderd = true');

	}

	public function getTaak($tid) {
		$taak = $this->retrieveByPrimaryKey(array($tid));
		/** @var CorveeTaak $taak */
		if ($taak->verwijderd) {
			throw new CsrGebruikerException('Maaltijd is verwijderd');
		}
		return $taak;
	}

	/**
	 * Haalt de taken op voor het ingelode lid of alle leden tussen de opgegeven data.
	 *
	 * @param int $van Timestamp
	 * @param int $tot Timestamp
	 * @param bool $iedereen
	 * @return CorveeTaak[]
	 * @throws CsrException
	 */
	public function getTakenVoorAgenda($van, $tot, $iedereen = false) {
		if (!is_int($van)) {
			throw new CsrException('Invalid timestamp: $van getTakenVoorAgenda()');
		}
		if (!is_int($tot)) {
			throw new CsrException('Invalid timestamp: $tot getTakenVoorAgenda()');
		}
		$where = 'verwijderd = FALSE AND datum >= ? AND datum <= ?';
		$values = array(date('Y-m-d', $van), date('Y-m-d', $tot));
		if (!$iedereen) {
			$where .= ' AND uid = ?';
			$values[] = LoginModel::getUid();
		}
		return $this->find($where, $values)->fetchAll();
	}

	/**
	 * Haalt de taken op voor een lid.
	 *
	 * @param string $uid
	 * @return PDOStatement|CorveeTaak[]
	 */
	public function getTakenVoorLid($uid) {
		return $this->find('verwijderd = false AND uid = ?', array($uid));
	}

	/**
	 * Zoekt de laatste taak op van een lid.
	 *
	 * @param string $uid
	 * @return CorveeTaak
	 */
	public function getLaatsteTaakVanLid($uid) {
		return $this->find('verwijderd = false AND uid = ?', array($uid), null, 'datum DESC', 1)->fetch();
	}

	/**
	 * Haalt de komende taken op waarvoor een lid is ingedeeld.
	 *
	 * @param string $uid
	 * @return PDOStatement|CorveeTaak[]
	 */
	public function getKomendeTakenVoorLid($uid) {
		return $this->find('verwijderd = false AND uid = ? AND datum >= ?', array($uid, date('Y-m-d')));
	}

	public function saveTaak($tid, $fid, $uid, $crid, $mid, $datum, $punten, $bonus_malus) {
		return Database::transaction(function () use ($tid, $fid, $uid, $crid, $mid, $datum, $punten, $bonus_malus) {
			if ($tid === 0) {
				$taak = $this->newTaak($fid, $uid, $crid, $mid, $datum, $punten, $bonus_malus);
			} else {
				$taak = $this->getTaak($tid);
				if ($taak->functie_id !== $fid) {
					$taak->crv_repetitie_id = null;
					$taak->functie_id = $fid;
				}
				$taak->maaltijd_id = $mid;
				$taak->datum = $datum;
				$taak->punten = $punten;
				$taak->bonus_malus = $bonus_malus;
				if (!$this->taakToewijzenAanLid($taak, $uid)) {
					$this->update($taak);
				}
			}

			return $taak;
		});
	}

	public function herstelTaak($tid) {
		/** @var CorveeTaak $taak */
		$taak = $this->retrieveByPrimaryKey(array($tid));
		if (!$taak->verwijderd) {
			throw new CsrGebruikerException('Corveetaak is niet verwijderd');
		}
		$taak->verwijderd = false;
		$this->update($taak);
		return $taak;
	}

	public function prullenbakLeegmaken() {
		$taken = $this->find('verwijderd = true');
		foreach ($taken as $taak) {
			$this->delete($taak);
		}
		return $taken->rowCount();
	}

	public function verwijderOudeTaken() {
		$taken = $this->find('datum < ?', array(date('Y-m-d')));
		foreach ($taken as $taak) {
			$taak->verwijderd = true;
			$this->update($taak);
		}
		return $taken->rowCount();
	}

	public function verwijderTakenVoorLid($uid) {
		$taken = $this->find('uid = ? AND datum >= ?', array($uid, date('Y-m-d')));
		foreach ($taken as $taak) {
			$this->delete($taak);
		}
		return $taken->rowCount();
	}

	public function verwijderTaak($tid) {
		/** @var CorveeTaak $taak */
		$taak = $this->retrieveByPrimaryKey(array($tid));
		if ($taak->verwijderd) {
			$this->delete($taak); // definitief verwijderen
		} else {
			$taak->verwijderd = true;
			$this->update($taak);
		}
	}

	public function vanRepetitie(CorveeRepetitie $repetitie, $datum, $mid = null, $uid = null, $bonus_malus = 0) {
		$taak = new CorveeTaak();
		$taak->taak_id = null;
		$taak->functie_id = $repetitie->functie_id;
		$taak->uid = $uid;
		$taak->crv_repetitie_id = $repetitie->crv_repetitie_id;
		$taak->maaltijd_id = $mid;
		$taak->datum = $datum;
		$taak->bonus_malus = $bonus_malus;
		$taak->punten = $repetitie->standaard_punten;
		$taak->punten_toegekend = 0;
		$taak->bonus_toegekend = 0;
		$taak->wanneer_toegekend = null;
		$taak->wanneer_gemaild = '';
		$taak->verwijderd = false;
		return $taak;

	}

	private function newTaak($fid, $uid, $crid, $mid, $datum, $punten, $bonus_malus) {
		$taak = new CorveeTaak();
		$taak->taak_id = null;
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
		$taak->wanneer_gemaild = '';
		$taak->verwijderd = false;

		$this->create($taak);

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
	 * @throws CsrGebruikerException
	 */
	public function getTakenVoorMaaltijd($mid, $verwijderd = false) {
		if ($mid <= 0) {
			throw new CsrGebruikerException('Load taken voor maaltijd faalt: Invalid $mid =' . $mid);
		}
		if ($verwijderd) {
			return $this->find('maaltijd_id = ?', array($mid));
		}
		return $this->find('verwijderd = false AND maaltijd_id = ?', array($mid));
	}

	/**
	 * Called when a Maaltijd is going to be deleted.
	 *
	 * @param int $mid
	 * @return bool
	 */
	public function existMaaltijdCorvee($mid) {
		return $this->count('maaltijd_id = ?', array($mid)) > 0;
	}

	/**
	 * Called when a Maaltijd is going to be deleted.
	 *
	 * @param int $mid
	 * @return int
	 */
	public function verwijderMaaltijdCorvee($mid) {
		$taken = $this->find('maaltijd_id = ?', array($mid));
		foreach ($taken as $taak) {
			$taak->verwijderd = true;
			$this->update($taak);
		}
		return $taken->rowCount();
	}

	// Functie-Taken ############################################################

	/**
	 * Called when a CorveeFunctie is going to be deleted.
	 *
	 * @param int $fid
	 * @return bool
	 */
	public function existFunctieTaken($fid) {
		return $this->count('functie_id = ?', array($fid)) > 0;
	}

	// Repetitie-Taken ############################################################

	public function maakRepetitieTaken(CorveeRepetitie $repetitie, $beginDatum, $eindDatum, $mid = null) {
		if ($repetitie->periode_in_dagen < 1) {
			throw new CsrGebruikerException('New repetitie-taken faalt: $periode =' . $repetitie->periode_in_dagen);
		}

		return Database::transaction(function () use ($repetitie, $beginDatum, $eindDatum, $mid) {
			return $this->newRepetitieTaken($repetitie, strtotime($beginDatum), strtotime($eindDatum), $mid);
		});
	}

	public function newRepetitieTaken(CorveeRepetitie $repetitie, $beginDatum, $eindDatum, $mid = null) {
		// start at first occurence
		$shift = $repetitie->dag_vd_week - date('w', $beginDatum) + 7;
		$shift %= 7;
		if ($shift > 0) {
			$beginDatum = strtotime('+' . $shift . ' days', $beginDatum);
		}
		$datum = $beginDatum;
		$taken = array();
		while ($datum <= $eindDatum) { // break after one
			for ($i = $repetitie->standaard_aantal; $i > 0; $i--) {
				$taak = $this->vanRepetitie($repetitie, date('Y-m-d', $datum), $mid, null, 0);
				$this->create($taak);
				$taken[] = $taak;
			}
			if ($repetitie->periode_in_dagen < 1) {
				break;
			}
			$datum = strtotime('+' . $repetitie->periode_in_dagen . ' days', $datum);
		}
		return $taken;
	}

	public function verwijderRepetitieTaken($crid) {
		$taken = $this->find('crv_repetitie_id = ?', array($crid));
		foreach ($taken as $taak) {
			$taak->verwijderd = true;
			$this->update($taak);
		}

		return $taken->rowCount();
	}

	/**
	 * Called when a CorveeRepetitie is updated or is going to be deleted.
	 *
	 * @param int $crid
	 * @return bool
	 */
	public function existRepetitieTaken($crid) {
		return $this->count('crv_repetitie_id = ?', array($crid)) > 0;
	}

	public function updateRepetitieTaken(CorveeRepetitie $repetitie, $verplaats) {
		return Database::transaction(function () use ($repetitie, $verplaats) {
			$taken = $this->find('verwijderd = false AND crv_repetitie_id = ?', array($repetitie->crv_repetitie_id));
			/** @var CorveeTaak $taak */

			foreach ($taken as $taak) {
				$taak->functie_id = $repetitie->functie_id;
				$taak->punten = $repetitie->standaard_punten;

				$this->update($taak);
			}
			$updatecount = $taken->rowCount();

			$taken = $this->find('verwijderd = FALSE AND crv_repetitie_id = ?', array($repetitie->crv_repetitie_id));
			$takenPerDatum = array(); // taken per datum indien geen maaltijd
			$takenPerMaaltijd = array(); // taken per maaltijd
			$maaltijden = MaaltijdenModel::instance()->getKomendeRepetitieMaaltijden($repetitie->mlt_repetitie_id);
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
					$shift = $repetitie->dag_vd_week - date('w', $datum);
					if ($shift > 0) {
						$datum = strtotime('+' . $shift . ' days', $datum);
					} elseif ($shift < 0) {
						$datum = strtotime($shift . ' days', $datum);
					}
					if ($shift !== 0) {
						$taak->datum = date('Y-m-d', $datum);
						$this->update($taak);
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
				$verschil = $repetitie->standaard_aantal - sizeof($taken);
				for ($i = $verschil; $i > 0; $i--) {
					$taak = $this->vanRepetitie($repetitie, $taken[0]->datum, null, null, 0);
					$this->create($taak);
				}
				$datumcount += $verschil;
			}
			$maaltijdcount = 0;
			foreach ($takenPerMaaltijd as $mid => $taken) {
				$verschil = $repetitie->standaard_aantal - sizeof($taken);
				for ($i = $verschil; $i > 0; $i--) {
					$taak = $this->vanRepetitie($repetitie, $maaltijdenById[$mid]->datum, $mid, null, 0);
					$this->create($taak);
				}
				$maaltijdcount += $verschil;
			}
			return array('update' => $updatecount, 'day' => $daycount, 'datum' => $datumcount, 'maaltijd' => $maaltijdcount);
		});
	}

}
