<?php

require_once 'model/entity/maalcie/CorveeVoorkeur.class.php';
require_once 'model/maalcie/CorveeRepetitiesModel.class.php';

/**
 * CorveeVoorkeurenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class CorveeVoorkeurenModel extends PersistenceModel {
	const ORM = "CorveeVoorkeur";
	const DIR = "maalcie/";

	protected static $instance;

	public function getEetwens(Profiel $profiel) {
		return $profiel->eetwens;
	}

	public function setEetwens(Profiel $profiel, $eetwens) {
		$profiel->eetwens = $eetwens;
		if (ProfielModel::instance()->update($profiel) !== 1) {
			throw new Exception('Eetwens opslaan mislukt');
		}
	}

	/**
	 * Geeft de ingeschakelde voorkeuren voor een lid terug en ook
	 * de voorkeuren die het lid nog kan inschakelen.
	 * Dat laatste kan alleen voor het ingelogde lid.
	 * Voor elk ander lid worden de permissies niet gefilterd.
	 * 
	 * @param string $uid
	 * @param boolean $uitgeschakeld 
	 * @return CorveeVoorkeur[]
	 */
	public function getVoorkeurenVoorLid($uid, $uitgeschakeld = false) {
		$repById = CorveeRepetitiesModel::getVoorkeurbareRepetities(true); // grouped by crid
		$lijst = array();
		$voorkeuren = $this->find('uid = ?', array($uid)); /** @var CorveeVoorkeur[] $voorkeuren */
		foreach ($voorkeuren as $voorkeur) {
			$crid = $voorkeur->crv_repetitie_id;
			if (!array_key_exists($crid, $repById)) { // ingeschakelde voorkeuren altijd weergeven
				$repById[$crid] = CorveeRepetitiesModel::getRepetitie($crid);
			}
			$voorkeur->setCorveeRepetitie($repById[$crid]);
			$voorkeur->setVanUid($uid);
			$lijst[$crid] = $voorkeur;
		}
		foreach ($repById as $crid => $repetitie) {
			if ($repetitie->getCorveeFunctie()->kwalificatie_benodigd) {
				require_once 'model/maalcie/KwalificatiesModel.class.php';
				if (!KwalificatiesModel::instance()->isLidGekwalificeerdVoorFunctie($uid, $repetitie->getFunctieId())) {
					continue;
				}
			}
			if (!array_key_exists($crid, $lijst)) { // uitgeschakelde voorkeuren weergeven
				if ($uitgeschakeld) {
					$voorkeur = new CorveeVoorkeur();
					$voorkeur->crv_repetitie_id = $crid;
					$voorkeur->uid = null;
					$voorkeur->setCorveeRepetitie($repetitie);
					$voorkeur->setVanUid($uid);
					$lijst[$crid] = $voorkeur;
				}
			}
		}
		ksort($lijst);
		return $lijst;
	}

	public function getHeeftVoorkeur($crid, $uid) {
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = $crid;
		$voorkeur->uid = $uid;

		return $this->exists($voorkeur);
	}

	/**
	 * Bouwt matrix voor alle repetities en voorkeuren van alle leden
	 * 
	 * @return CorveeVoorkeur[uid][crid]
	 */
	public function getVoorkeurenMatrix() {
		$repById = CorveeRepetitiesModel::getVoorkeurbareRepetities(true); // grouped by crid
		$leden_voorkeuren = $this->loadLedenVoorkeuren();
		$matrix = array();
		foreach ($leden_voorkeuren as $lv) { // build matrix
			$crid = $lv['crid'];
			$uid = $lv['van'];
			$voorkeur = new CorveeVoorkeur();
			$voorkeur->crv_repetitie_id = $crid;
			if ($lv['voorkeur']) { // ingeschakelde voorkeuren
				$voorkeur->uid = $uid;
			} else { // uitgeschakelde voorkeuren
				$voorkeur->uid = null;
			}
			$voorkeur->setCorveeRepetitie($repById[$crid]);
			$voorkeur->setVanUid($uid);
			$matrix[$uid][$crid] = $voorkeur;
			ksort($repById);
			ksort($matrix[$uid]);
		}
		return array($matrix, $repById);
	}

	private function loadLedenVoorkeuren() {
		$sql = 'SELECT lid.uid AS van, r.crv_repetitie_id AS crid, ';
		$sql.= ' (EXISTS (SELECT * FROM crv_voorkeuren AS v WHERE v.crv_repetitie_id = crid AND v.uid = van )) AS voorkeur';
		$sql.= ' FROM profielen AS lid, crv_repetities AS r';
		$sql.= ' WHERE r.voorkeurbaar = true AND lid.status IN("S_LID", "S_GASTLID", "S_NOVIET")'; // alleen leden
		$sql.= ' ORDER BY lid.achternaam, lid.voornaam ASC';
		$db = \Database::instance();
		$values = array();
		$query = $db->prepare($sql);
		$query->execute($values);
		$result = $query->fetchAll();
		return $result;
	}

	public function getVoorkeurenVoorRepetitie($crid) {
		if (!is_int($crid) || $crid <= 0) {
			throw new Exception('Get voorkeuren voor repetitie faalt: Invalid $crid = ' . $crid);
		}
		return $this->find('crv_repetitie_id = ?', array($crid));
	}

	public function inschakelenVoorkeur($crid, $uid) {
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = $crid;
		$voorkeur->uid = $uid;

		if ($this->exists($voorkeur)) {
			throw new Exception('Voorkeur al ingeschakeld');
		}
		$repetitie = CorveeRepetitiesModel::getRepetitie($crid);
		if (!$repetitie->getIsVoorkeurbaar()) {
			throw new Exception('Niet voorkeurbaar');
		}
		if ($repetitie->getCorveeFunctie()->kwalificatie_benodigd) {
			require_once 'model/maalcie/KwalificatiesModel.class.php';
			if (!KwalificatiesModel::instance()->isLidGekwalificeerdVoorFunctie($uid, $repetitie->getFunctieId())) {
				throw new Exception('Niet gekwalificeerd');
			}
		}

		$this->create($voorkeur);

		return $voorkeur;
	}

	public function uitschakelenVoorkeur($crid, $uid) {
		$voorkeur = new CorveeVoorkeur();
		$voorkeur->crv_repetitie_id = $crid;
		$voorkeur->uid = $uid;

		if (!$this->exists($voorkeur)) {
			throw new Exception('Voorkeur al uitgeschakeld');
		}

		$this->delete($voorkeur);

		return $voorkeur;
	}

	/**
	 * Called when a CorveeRepetitie is being deleted.
	 * This is only possible after all CorveeVoorkeuren are deleted of this CorveeRepetitie (db foreign key)
	 * 
	 * @return int amount of deleted voorkeuren
	 */
	public function verwijderVoorkeuren($crid) {
		$voorkeuren = $this->find('crv_repetitie_id = ?', array($crid));
		$num = $voorkeuren->rowCount();
		foreach ($voorkeuren as $voorkeur) {
			$this->delete($voorkeur);
		}

		return $num;
	}

	/**
	 * Called when a Lid is being made Lid-af.
	 * 
	 * @return int amount of deleted voorkeuren
	 */
	public function verwijderVoorkeurenVoorLid($uid) {
		$voorkeuren = $this->find('uid = ?', array($uid));
		$aantal = $voorkeuren->rowCount();
		foreach ($voorkeuren as $voorkeur) {
			$this->delete($voorkeur);
		}
		return $aantal;
	}

}

?>