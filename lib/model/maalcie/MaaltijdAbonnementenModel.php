<?php

namespace CsrDelft\model\maalcie;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\model\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\model\ProfielModel;
use CsrDelft\Orm\Persistence\Database;
use CsrDelft\Orm\PersistenceModel;

/**
 * MaaltijdAbonnementenModel.class.php    |    P.W.G. Brussee (brussee@live.nl)
 *
 */
class MaaltijdAbonnementenModel extends PersistenceModel {

	const ORM = MaaltijdAbonnement::class;

	/**
	 * Geeft de ingeschakelde abonnementen voor een lid terug plus
	 * de abonnementen die nog kunnen worden ingeschakeld op basis
	 * van de meegegeven maaltijdrepetities.
	 *
	 * @param string $uid
	 * @param boolean $abonneerbaar alleen abonneerbare abonnementen
	 * @param boolean $uitgeschakeld ook uitgeschakelde abonnementen
	 * @return MaaltijdAbonnement[]
	 */
	public function getAbonnementenVoorLid($uid, $abonneerbaar = false, $uitgeschakeld = false) {
		return Database::transaction(function () use ($uid, $abonneerbaar, $uitgeschakeld) {
			if ($abonneerbaar) {
				$repById = MaaltijdRepetitiesModel::instance()->getAbonneerbareRepetitiesVoorLid($uid); // grouped by mrid
			} else {
				$repById = MaaltijdRepetitiesModel::instance()->getAlleRepetities(true); // grouped by mrid
			}
			$lijst = array();
			$abos = $this->find('uid = ?', array($uid));
			foreach ($abos as $abo) { // ingeschakelde abonnementen
				$mrid = $abo->mlt_repetitie_id;
				if (!array_key_exists($mrid, $repById)) { // ingeschakelde abonnementen altijd weergeven
					$repById[$mrid] = MaaltijdRepetitiesModel::instance()->getRepetitie($mrid);
				}
				$abo->maaltijd_repetitie = $repById[$mrid];
				$abo->van_uid = $uid;
				$lijst[$mrid] = $abo;
			}
			if ($uitgeschakeld) {
				foreach ($repById as $repetitie) {
					$mrid = $repetitie->mlt_repetitie_id;
					if (!array_key_exists($mrid, $lijst)) { // uitgeschakelde abonnementen weergeven
						$abo = new MaaltijdAbonnement();
						$abo->mlt_repetitie_id = $repetitie->mlt_repetitie_id;
						$abo->maaltijd_repetitie = $repetitie;
						$abo->van_uid = $uid;
						$lijst[$mrid] = $abo;
					}
				}
			}
			ksort($lijst);
			return $lijst;
		});
	}

	public function getHeeftAbonnement($mrid, $uid) {
		$abonnement = new MaaltijdAbonnement();
		$abonnement->mlt_repetitie_id = $mrid;
		$abonnement->uid = $uid;
		return $this->exists($abonnement);
	}

	public function getAbonnementenWaarschuwingenMatrix() {
		return Database::transaction(function () {
			$abos = $this->find();

			$waarschuwingen = array();

			foreach ($abos as $abo) {
				$repetitie = MaaltijdRepetitiesModel::instance()->getRepetitie($abo->mlt_repetitie_id);
				if (!MaaltijdAanmeldingenModel::instance()->checkAanmeldFilter($abo->uid, $repetitie->abonnement_filter)) {
					$abo->foutmelding = 'Niet toegestaan vanwege aanmeldrestrictie: ' . $repetitie->abonnement_filter;
					$waarschuwingen[$abo->uid][$abo->mlt_repetitie_id] = $abo;
				} elseif (!$repetitie->abonneerbaar) {
					$abo->foutmelding = 'Niet abonneerbaar';
					$waarschuwingen[$abo->uid][$abo->mlt_repetitie_id] = $abo;
				} elseif (!LidStatus::isLidLike(ProfielModel::get($abo->uid)->status)) {
					$abo->waarschuwing = 'Geen huidig lid';
					$waarschuwingen[$abo->uid][$abo->mlt_repetitie_id] = $abo;
				}
			}

			$repById = MaaltijdRepetitiesModel::instance()->getAlleRepetities(true);

			return $this->fillHoles($waarschuwingen, $repById);
		});
	}

	public function getAbonnementenAbonneerbaarMatrix() {
		return Database::transaction(function () {
			$repById = MaaltijdRepetitiesModel::instance()->getAlleRepetities(true); // grouped by mrid
			$sql = 'SELECT lid.uid AS van, r.mlt_repetitie_id AS mrid,';
			$sql .= ' r.abonnement_filter AS filter,'; // controleer later
			$sql .= ' (r.abonneerbaar = false) AS abo_err, (lid.status NOT IN("S_LID", "S_GASTLID", "S_NOVIET")) AS status_err,';
			$sql .= ' (EXISTS ( SELECT * FROM mlt_abonnementen AS a WHERE a.mlt_repetitie_id = mrid AND a.uid = van )) AS abo';
			$sql .= ' FROM profielen AS lid, mlt_repetities AS r';
			$values = array();
			$sql .= ' WHERE lid.status IN("S_LID", "S_GASTLID", "S_NOVIET")';
			$sql .= ' ORDER BY lid.achternaam, lid.voornaam ASC';
			$db = Database::instance()->getDatabase();
			$query = $db->prepare($sql);
			$query->execute($values);

			$leden = ProfielModel::instance()->find("status = ? OR status = ? OR status = ?", LidStatus::getLidLike());

			$matrix = array();
			foreach ($leden as $lid) {
				$abos = $this->find("uid = ?", array($lid->uid));
				foreach ($abos as $abo) {
					$rep = $repById[$abo->mlt_repetitie_id];
					$abo->maaltijd_repetitie = $rep;
					if (!MaaltijdAanmeldingenModel::instance()->checkAanmeldFilter($lid->uid, $rep->abonnement_filter)) {
						$abo->foutmelding = 'Niet toegestaan vanwege aanmeldrestrictie: ' . $rep->abonnement_filter;
					}
					$matrix[$lid->uid][$abo->mlt_repetitie_id] = $abo;
				}
			}

			return $this->fillHoles($matrix, $repById);
		});
	}

	/**
	 * Bouwt matrix voor alle repetities en abonnementen van alle leden
	 *
	 * @return MaaltijdAbonnement[][] 2d matrix met eerst uid, en dan repetitie id
	 */
	public function getAbonnementenMatrix() {
		return Database::transaction(function () {
			$repById = MaaltijdRepetitiesModel::instance()->getAlleRepetities(true); // grouped by mrid
			$sql = 'SELECT lid.uid AS van, r.mlt_repetitie_id AS mrid,';
			$sql .= ' r.abonnement_filter AS filter,'; // controleer later
			$sql .= ' (r.abonneerbaar = false) AS abo_err, (lid.status NOT IN("S_LID", "S_GASTLID", "S_NOVIET")) AS status_err,';
			$sql .= ' (EXISTS ( SELECT * FROM mlt_abonnementen AS a WHERE a.mlt_repetitie_id = mrid AND a.uid = van )) AS abo';
			$sql .= ' FROM profielen AS lid, mlt_repetities AS r';
			$sql .= ' HAVING abo = true';
			$sql .= ' ORDER BY lid.achternaam, lid.voornaam ASC';
			$db = Database::instance()->getDatabase();
			$query = $db->prepare($sql);
			$query->execute();
			$abos = $query->fetchAll();

			$matrix = array();
			foreach ($abos as $abo) { // build matrix
				$mrid = $abo['mrid'];
				$uid = $abo['van'];
				if ($abo['abo']) { // ingeschakelde abonnementen
					$abonnement = new MaaltijdAbonnement();
					$abonnement->mlt_repetitie_id = $mrid;
					$abonnement->uid = $uid;
				} else { // uitgeschakelde abonnementen
					$abonnement = new MaaltijdAbonnement();
					$abonnement->mlt_repetitie_id = $mrid;
				}
				$abonnement->van_uid = $uid;
				$abonnement->maaltijd_repetitie = $repById[$mrid];
				// toon waarschuwingen
				if ($abo['abo_err']) {
					$abonnement->foutmelding = 'Niet abonneerbaar';
				} elseif ($abo['status_err']) {
					$abonnement->waarschuwing = 'Geen huidig lid';
				} elseif (!MaaltijdAanmeldingenModel::instance()->checkAanmeldFilter($uid, $abo['filter'])) {
					$abonnement->foutmelding = 'Niet toegestaan vanwege aanmeldrestrictie: ' . $abo['filter'];
				}
				$matrix[$uid][$mrid] = $abonnement;
			}
			return $this->fillHoles($matrix, $repById, true);
		});
	}

	/**
	 * @param $matrix
	 * @param $repById
	 * @param bool $ingeschakeld
	 * @return array
	 */
	private function fillHoles($matrix, $repById, $ingeschakeld = false) {
		foreach ($repById as $mrid => $repetitie) { // vul gaten in matrix vanwege uitgeschakelde abonnementen
			foreach ($matrix as $uid => $abos) {
				if (!array_key_exists($mrid, $abos)) {
					$abonnement = new MaaltijdAbonnement();
					$abonnement->mlt_repetitie_id = $ingeschakeld ? $mrid : null;
					$abonnement->van_uid = $uid;
					$abonnement->maaltijd_repetitie = $repetitie;
					$matrix[$uid][$mrid] = $abonnement;
				}
				ksort($repById);
				ksort($matrix[$uid]);
			}
		}
		return array($matrix, $repById);
	}

	public function getAbonnementenVoorRepetitie($mrid) {
		return $this->find('mlt_repetitie_id = ?', array($mrid));
	}

	public function getAbonnementenVanNovieten() {
		return Database::transaction(function () {
			$novieten = ProfielModel::instance()->find('status = "S_NOVIET"');
			$matrix = array();
			foreach ($novieten as $noviet) {
				$matrix[$noviet->uid] = $this->find('uid = ?', array($noviet->uid), null, 'mlt_repetitie_id')->fetchAll();
			}
			return $matrix;
		});
	}

	/**
	 * @param $abo MaaltijdAbonnement
	 * @return false|int
	 * @throws CsrGebruikerException
	 */
	public function inschakelenAbonnement($abo) {
		return Database::transaction(function () use ($abo) {
			$repetitie = MaaltijdRepetitiesModel::instance()->getRepetitie($abo->mlt_repetitie_id);
			if (!$repetitie->abonneerbaar) {
				throw new CsrGebruikerException('Niet abonneerbaar');
			}
			if ($this->exists($abo)) {
				throw new CsrGebruikerException('Abonnement al ingeschakeld');
			}
			if (!MaaltijdAanmeldingenModel::instance()->checkAanmeldFilter($abo->uid, $repetitie->abonnement_filter)) {
				throw new CsrGebruikerException('Niet toegestaan vanwege aanmeldrestrictie: ' . $repetitie->abonnement_filter);
			}

			$abo->van_uid = $abo->uid;
			$abo->wanneer_ingeschakeld = date('Y-m-d H:i');
			$this->create($abo);

			$aantal = MaaltijdAanmeldingenModel::instance()->aanmeldenVoorKomendeRepetitieMaaltijden($abo->mlt_repetitie_id, $abo->uid);
			return $aantal;
		});
	}

	public function inschakelenAbonnementVoorNovieten($mrid) {
		return Database::transaction(function () use ($mrid) {
			$novieten = ProfielModel::instance()->find('status = "S_NOVIET"');

			$aantal = 0;
			foreach ($novieten as $noviet) {
				$repetitie = MaaltijdRepetitiesModel::instance()->getRepetitie($mrid);
				if (!MaaltijdAanmeldingenModel::instance()->checkAanmeldFilter($noviet->uid, $repetitie->abonnement_filter)) {
					continue;
				}

				$abo = new MaaltijdAbonnement();
				$abo->mlt_repetitie_id = $mrid;
				$abo->uid = $noviet->uid;
				$abo->wanneer_ingeschakeld = date('Y-m-d H:i');

				if ($this->exists($abo)) {
					continue;
				}
				$this->create($abo);
				MaaltijdAanmeldingenModel::instance()->aanmeldenVoorKomendeRepetitieMaaltijden($mrid, $noviet->uid);
				$aantal += 1;
			}

			return $aantal;
		});
	}

	public function uitschakelenAbonnement($mrid, $uid) {
		return Database::transaction(function () use ($mrid, $uid) {
			if (!$this->getHeeftAbonnement($mrid, $uid)) {
				throw new CsrGebruikerException('Abonnement al uitgeschakeld');
			}
			$this->deleteByPrimaryKey(array($mrid, $uid));
			$abo = new MaaltijdAbonnement();
			$abo->mlt_repetitie_id = $mrid;
			$abo->van_uid = $uid;

			$aantal = MaaltijdAanmeldingenModel::instance()->afmeldenDoorAbonnement($mrid, $uid);
			return array($abo, $aantal);
		});
	}

	/**
	 * Called when a MaaltijdRepetitie is being deleted.
	 * This is only possible after all MaaltijdAanmeldingen are deleted of this MaaltijdAbonnement,
	 * by deleting the Maaltijden (db foreign key door_abonnement)
	 *
	 * @param $mrid
	 * @return int amount of deleted abos
	 */
	public function verwijderAbonnementen($mrid) {
		return Database::transaction(function () use ($mrid) {
			/** @var MaaltijdAbonnement[] $abos */
			$abos = $this->find('mlt_repetitie_id = ?', array($mrid))->fetchAll();
			$aantal = count($abos);
			foreach ($abos as $abo) {
				MaaltijdAanmeldingenModel::instance()->afmeldenDoorAbonnement($mrid, $abo->uid);
				$this->delete($abo);
			}
			return $aantal;
		});
	}

	/**
	 * Called when a Lid is being made Lid-af.
	 * All linked MaaltijdAanmeldingen are deleted of this MaaltijdAbonnement.
	 *
	 * @param $uid
	 * @return int amount of deleted abos
	 */
	public function verwijderAbonnementenVoorLid($uid) {
		return Database::transaction(function () use ($uid) {
			$abos = $this->getAbonnementenVoorLid($uid);
			$aantal = 0;
			foreach ($abos as $abo) {
				$aantal += $this->delete($abo);
			}
			if (sizeof($abos) !== $aantal) {
				setMelding('Niet alle abonnementen zijn uitgeschakeld!', -1);
			}
			return $aantal;
		});
	}
}
