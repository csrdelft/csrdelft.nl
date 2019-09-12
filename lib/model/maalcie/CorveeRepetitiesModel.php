<?php

namespace CsrDelft\model\maalcie;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\maalcie\CorveeRepetitie;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;
use PDOStatement;


/**
 * CorveeRepetitiesModel.class.php  |  P.W.G. Brussee (brussee@live.nl)
 *
 */
class CorveeRepetitiesModel extends PersistenceModel {
	const ORM = CorveeRepetitie::class;

	public function nieuw($crid = 0, $mrid = null, $dag = null, $periode = null, $fid = 0, $punten = 0, $aantal = null, $voorkeur = null) {
		$repetitie = new CorveeRepetitie();
		$repetitie->crv_repetitie_id = (int)$crid;
		$repetitie->mlt_repetitie_id = $mrid;
		if ($dag === null) {
			$dag = intval(instelling('corvee', 'standaard_repetitie_weekdag'));
		}
		$repetitie->dag_vd_week = $dag;
		if ($periode === null) {
			$periode = intval(instelling('corvee', 'standaard_repetitie_periode'));
		}
		$repetitie->periode_in_dagen = $periode;
		$repetitie->functie_id = $fid;
		$repetitie->standaard_punten = $punten;
		if ($aantal === null) {
			$aantal = intval(instelling('corvee', 'standaard_aantal_corveers'));
		}
		$repetitie->standaard_aantal = $aantal;
		if ($voorkeur === null) {
			$voorkeur = (boolean)instelling('corvee', 'standaard_voorkeurbaar');
		}
		$repetitie->voorkeurbaar = $voorkeur;

		return $repetitie;
	}

	public function getFirstOccurrence(CorveeRepetitie $repetitie) {
		$datum = time();
		$shift = $repetitie->dag_vd_week - date('w', $datum) + 7;
		$shift %= 7;
		if ($shift > 0) {
			$datum = strtotime('+' . $shift . ' days', $datum);
		}
		return date('Y-m-d', $datum);
	}

	public function getVoorkeurbareRepetities() {
		$repetities = $this->find('voorkeurbaar = true');
		$result = array();
		foreach ($repetities as $repetitie) {
			$result[$repetitie->crv_repetitie_id] = $repetitie;
		}
		return $result;
	}

	public function getAlleRepetities() {
		return $this->find();
	}

	/**
	 * Haalt de periodieke taken op die gekoppeld zijn aan een periodieke maaltijd.
	 *
	 * @param int $mrid
	 * @return PDOStatement|CorveeRepetitie[]
	 */
	public function getRepetitiesVoorMaaltijdRepetitie($mrid) {
		return $this->find('mlt_repetitie_id = ?', array($mrid));
	}

	/**
	 * @param $crid
	 * @return CorveeRepetitie|false
	 */
	public function getRepetitie($crid) {
		return $this->retrieveByPrimaryKey(array($crid));
	}

	public function saveRepetitie($crid, $mrid, $dag, $periode, $fid, $punten, $aantal, $voorkeur) {
		return Database::transaction(function () use ($crid, $mrid, $dag, $periode, $fid, $punten, $aantal, $voorkeur) {
			$voorkeuren = 0;
			if ($crid === 0) {
				$repetitie = $this->nieuw(0, $mrid, $dag, $periode, $fid, $punten, $aantal, $voorkeur);
				$repetitie->crv_repetitie_id = $this->create($repetitie);
			} else {
				$repetitie = $this->getRepetitie($crid);
				$repetitie->mlt_repetitie_id = $mrid;
				$repetitie->dag_vd_week = $dag;
				$repetitie->periode_in_dagen = $periode;
				$repetitie->functie_id = $fid;
				$repetitie->standaard_punten = $punten;
				$repetitie->standaard_aantal = $aantal;
				$repetitie->voorkeurbaar = (boolean)$voorkeur;
				$this->update($repetitie);
				if (!$voorkeur) { // niet (meer) voorkeurbaar
					$voorkeuren = CorveeVoorkeurenModel::instance()->verwijderVoorkeuren($crid);
				}
			}
			return array($repetitie, $voorkeuren);
		});
	}

	public function verwijderRepetitie($crid) {
		if (is_numeric($crid) || $crid <= 0) {
			throw new CsrGebruikerException('Verwijder corvee-repetitie faalt: Invalid $crid =' . $crid);
		}
		if (CorveeTakenModel::instance()->existRepetitieTaken($crid)) {
			CorveeTakenModel::instance()->verwijderRepetitieTaken($crid); // delete corveetaken first (foreign key)
			throw new CsrGebruikerException('Alle bijbehorende corveetaken zijn naar de prullenbak verplaatst. Verwijder die eerst!');
		}

		return Database::transaction(function () use ($crid) {
			$aantal = CorveeVoorkeurenModel::instance()->verwijderVoorkeuren($crid); // delete voorkeuren first (foreign key)
			$deleted = $this->deleteByPrimaryKey(array($crid));
			if ($deleted !== 1) {
				throw new CsrException('Delete corvee-repetitie faalt: $deleted =' . $deleted);
			}
			return $aantal;
		});
	}

	// Maaltijd-Repetitie-Corvee ############################################################

	/**
	 * Called when a MaaltijdRepetitie is going to be deleted.
	 *
	 * @param int $mrid
	 * @return bool
	 */
	public function existMaaltijdRepetitieCorvee($mrid) {
		return $this->count('mlt_repetitie_id = ?', array($mrid)) > 0;
	}

	// Functie-Repetities ############################################################

	/**
	 * Called when a CorveeFunctie is going to be deleted.
	 *
	 * @param int $fid
	 * @return bool
	 */
	public function existFunctieRepetities($fid) {
		return $this->count('functie_id = ?', array($fid)) > 0;
	}

}
