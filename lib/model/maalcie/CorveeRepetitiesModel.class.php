<?php

use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;

require_once 'model/entity/maalcie/CorveeRepetitie.class.php';
require_once 'model/maalcie/CorveeTakenModel.class.php';
require_once 'model/maalcie/CorveeVoorkeurenModel.class.php';

/**
 * CorveeRepetitiesModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class CorveeRepetitiesModel extends PersistenceModel {
	const ORM = 'CorveeRepetitie';
	const DIR = 'maalcie/';

	protected static $instance;

	public function nieuw($crid = 0, $mrid = null, $dag = null, $periode = null, $fid = 0, $punten = 0, $aantal = null, $voorkeur = null) {
		$repetitie = new CorveeRepetitie();
		$repetitie->crv_repetitie_id = (int) $crid;
		$repetitie->mlt_repetitie_id = $mrid;
		if ($dag === null) {
			$dag = intval(Instellingen::get('corvee', 'standaard_repetitie_weekdag'));
		}
		$repetitie->dag_vd_week = $dag;
		if ($periode === null) {
			$periode = intval(Instellingen::get('corvee', 'standaard_repetitie_periode'));
		}
		$repetitie->periode_in_dagen = $periode;
		$repetitie->functie_id = $fid;
		$repetitie->standaard_punten = $punten;
		if ($aantal === null) {
			$aantal = intval(Instellingen::get('corvee', 'standaard_aantal_corveers'));
		}
		$repetitie->standaard_aantal = $aantal;
		if ($voorkeur === null) {
			$voorkeur = (boolean) Instellingen::get('corvee', 'standaard_voorkeurbaar');
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
	 * @throws Exception
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
		$db = Database::instance()->getDatabase();
		try {
			$db->beginTransaction();
			$voorkeuren = 0;
			if ($crid === 0) {
				$repetitie = $this->nieuw(0, $mrid, $dag, $periode, $fid, $punten, $aantal, $voorkeur);
				$repetitie->crv_repetitie_id = $this->create($repetitie);
			} else {
				$repetitie = $this->getRepetitie($crid);
				$repetitie->mlt_repetitie_id = $mrid;
				$repetitie->dag_vd_week =  $dag;
				$repetitie->periode_in_dagen = $periode;
				$repetitie->functie_id = $fid;
				$repetitie->standaard_punten = $punten;
				$repetitie->standaard_aantal = $aantal;
				$repetitie->voorkeurbaar = (boolean) $voorkeur;
				$this->update($repetitie);
				if (!$voorkeur) { // niet (meer) voorkeurbaar
					$voorkeuren = CorveeVoorkeurenModel::instance()->verwijderVoorkeuren($crid);
				}
			}
			$db->commit();
			return array($repetitie, $voorkeuren);
		} catch (\Exception $e) {
			$db->rollBack();
			throw $e; // rethrow to controller
		}
	}

	public function verwijderRepetitie($crid) {
		if (!is_int($crid) || $crid <= 0) {
			throw new Exception('Verwijder corvee-repetitie faalt: Invalid $crid =' . $crid);
		}
		if (CorveeTakenModel::instance()->existRepetitieTaken($crid)) {
			CorveeTakenModel::instance()->verwijderRepetitieTaken($crid); // delete corveetaken first (foreign key)
			throw new Exception('Alle bijbehorende corveetaken zijn naar de prullenbak verplaatst. Verwijder die eerst!');
		}

		$db = Database::instance()->getDatabase();
		try {
			$db->beginTransaction();
			$aantal = CorveeVoorkeurenModel::instance()->verwijderVoorkeuren($crid); // delete voorkeuren first (foreign key)
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
			$db->rollBack();
			throw $e; // rethrow to controller
		}
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

?>