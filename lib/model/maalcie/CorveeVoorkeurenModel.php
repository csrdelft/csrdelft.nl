<?php

namespace CsrDelft\model\maalcie;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\maalcie\CorveeVoorkeur;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\ProfielModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;

/**
 * CorveeVoorkeurenModel.class.php  |  P.W.G. Brussee (brussee@live.nl)
 *
 */
class CorveeVoorkeurenModel extends PersistenceModel {
	const ORM = CorveeVoorkeur::class;

	public function getEetwens(Profiel $profiel) {
		return $profiel->eetwens;
	}

	public function setEetwens(Profiel $profiel, $eetwens) {
		if ($profiel->eetwens === $eetwens) return;
		$profiel->eetwens = $eetwens;
		if (ProfielModel::instance()->update($profiel) !== 1) {
			throw new CsrException('Eetwens opslaan mislukt');
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
		$repById = CorveeRepetitiesModel::instance()->getVoorkeurbareRepetities(); // grouped by crid
		$lijst = array();
		/** @var CorveeVoorkeur[] $voorkeuren */
		$voorkeuren = $this->find('uid = ?', array($uid));
		foreach ($voorkeuren as $voorkeur) {
			$crid = $voorkeur->crv_repetitie_id;
			if (!array_key_exists($crid, $repById)) { // ingeschakelde voorkeuren altijd weergeven
				$repById[$crid] = CorveeRepetitiesModel::instance()->getRepetitie($crid);
			}
			$voorkeur->setCorveeRepetitie($repById[$crid]);
			$voorkeur->setVanUid($uid);
			$lijst[$crid] = $voorkeur;
		}
		foreach ($repById as $crid => $repetitie) {
			if ($repetitie->getCorveeFunctie()->kwalificatie_benodigd) {
				if (!KwalificatiesModel::instance()->isLidGekwalificeerdVoorFunctie($uid, $repetitie->functie_id)) {
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
	 * Bouwt matrix voor alle repetities en voorkeuren van alle leden in format CorveeVoorkeur[uid][crid]
	 *
	 * @return CorveeVoorkeur[][]
	 */
	public function getVoorkeurenMatrix() {
		$repById = CorveeRepetitiesModel::instance()->getVoorkeurbareRepetities(); // grouped by crid
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
		$sql .= ' (EXISTS (SELECT * FROM crv_voorkeuren AS v WHERE v.crv_repetitie_id = crid AND v.uid = van )) AS voorkeur';
		$sql .= ' FROM profielen AS lid, crv_repetities AS r';
		$sql .= ' WHERE r.voorkeurbaar = true AND lid.status IN("S_LID", "S_GASTLID", "S_NOVIET")'; // alleen leden
		$sql .= ' ORDER BY lid.achternaam, lid.voornaam ASC';
		$db = Database::instance()->getDatabase();
		$values = array();
		$query = $db->prepare($sql);
		$query->execute($values);
		return $query->fetchAll();
	}

	public function getVoorkeurenVoorRepetitie($crid) {
		if (!is_numeric($crid) || $crid <= 0) {
			throw new CsrGebruikerException('Get voorkeuren voor repetitie faalt: Invalid $crid = ' . $crid);
		}
		return $this->find('crv_repetitie_id = ?', array($crid));
	}

	public function inschakelenVoorkeur(CorveeVoorkeur $voorkeur) {
		if ($this->exists($voorkeur)) {
			throw new CsrGebruikerException('Voorkeur al ingeschakeld');
		}
		$repetitie = CorveeRepetitiesModel::instance()->getRepetitie($voorkeur->crv_repetitie_id);
		if (!$repetitie->voorkeurbaar) {
			throw new CsrGebruikerException('Niet voorkeurbaar');
		}
		if ($repetitie->getCorveeFunctie()->kwalificatie_benodigd) {
			if (!KwalificatiesModel::instance()->isLidGekwalificeerdVoorFunctie($voorkeur->uid, $repetitie->functie_id)) {
				throw new CsrGebruikerException('Niet gekwalificeerd');
			}
		}

		$this->create($voorkeur);

		return $voorkeur;
	}

	public function uitschakelenVoorkeur($voorkeur) {
		if (!$this->exists($voorkeur)) {
			throw new CsrGebruikerException('Voorkeur al uitgeschakeld');
		}

		$this->delete($voorkeur);

		$voorkeur->uid = null;

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
