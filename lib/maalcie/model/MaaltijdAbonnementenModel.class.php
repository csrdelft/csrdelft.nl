<?php

require_once 'maalcie/model/entity/MaaltijdAbonnement.class.php';
require_once 'maalcie/model/MaaltijdAanmeldingenModel.class.php';
require_once 'maalcie/model/MaaltijdRepetitiesModel.class.php';

/**
 * MaaltijdAbonnementenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MaaltijdAbonnementenModel {

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
	public static function getAbonnementenVoorLid($uid, $abonneerbaar = false, $uitgeschakeld = false) {
		if ($abonneerbaar) {
			$repById = MaaltijdRepetitiesModel::getAbonneerbareRepetitiesVoorLid($uid); // grouped by mrid
		} else {
			$repById = MaaltijdRepetitiesModel::getAlleRepetities(true); // grouped by mrid
		}
		$lijst = array();
		$abos = self::loadAbonnementen(null, $uid);
		foreach ($abos as $abo) { // ingeschakelde abonnementen
			$mrid = $abo->getMaaltijdRepetitieId();
			if (array_key_exists($mrid, $repById)) { // weergeven
				$abo->setMaaltijdRepetitie($repById[$mrid]);
				$abo->setVanUid($uid);
				$lijst[$mrid] = $abo;
			}
		}
		if ($uitgeschakeld) {
			foreach ($repById as $repetitie) {
				$mrid = $repetitie->getMaaltijdRepetitieId();
				if (!array_key_exists($mrid, $lijst)) { // uitgeschakelde abonnementen weergeven
					$abo = new MaaltijdAbonnement($repetitie->getMaaltijdRepetitieId(), null);
					$abo->setMaaltijdRepetitie($repetitie);
					$abo->setVanUid($uid);
					$lijst[$mrid] = $abo;
				}
			}
		}
		ksort($lijst);
		return $lijst;
	}

	public static function getHeeftAbonnement($mrid, $uid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new Exception('Get heeft abonnement faalt: Invalid $mrid =' . $mrid);
		}
		$sql = 'SELECT EXISTS (SELECT * FROM mlt_abonnementen WHERE mlt_repetitie_id=? AND uid=?)';
		$values = array($mrid, $uid);
		$query = \Database::instance()->prepare($sql);
		$query->execute($values);
		$result = $query->fetchColumn();
		return $result;
	}

	/**
	 * Bouwt matrix voor alle repetities en abonnementen van alle leden
	 * 
	 * @return MaaltijdAbonnement[uid][mrid]
	 */
	public static function getAbonnementenMatrix($repetities, $alleenNovieten = false, $alleenWaarschuwingen = false, $ingeschakeld = null, $voorLid = null) {
		$repById = array();
		foreach ($repetities as $repetitie) {
			$repById[$repetitie->getMaaltijdRepetitieId()] = $repetitie;
		}
		$abos = self::loadLedenAbonnementen($alleenNovieten, $alleenWaarschuwingen, $ingeschakeld, $voorLid);
		$matrix = array();
		foreach ($abos as $abo) { // build matrix
			$uid = $abo['uid'];
			$mrid = $abo['mrid'];
			if ($abo['abo']) { // ingeschakelde abonnementen
				$abonnement = new MaaltijdAbonnement($mrid, $uid);
			} else { // uitgeschakelde abonnementen
				$abonnement = new MaaltijdAbonnement($mrid, null);
			}
			$abonnement->setVanUid($uid);
			$abonnement->setMaaltijdRepetitie($repById[$mrid]);
			if ($alleenWaarschuwingen) {
				if ($abo['abo_err']) {
					$abonnement->setWaarschuwing('Niet abonneerbaar');
				} elseif ($abo['status_err']) {
					$abonnement->setWaarschuwing('Geen huidig lid');
				} elseif ($abo['kring_err']) {
					$abonnement->setWaarschuwing('Geen actief kringlid');
				} elseif (!MaaltijdAanmeldingenModel::checkAanmeldFilter($uid, $abo['abonnement_filter'])) {
					$abonnement->setWaarschuwing('Niet toegestaan vanwege aanmeldrestrictie: ' . $abo['abonnement_filter']);
				} else {
					continue;
					;
				}
			}
			$matrix[$uid][$mrid] = $abonnement;
		}
		foreach ($repById as $mrid => $repetitie) { // vul gaten in matrix vanwege uitgeschakelde abonnementen
			foreach ($matrix as $uid => $abos) {
				if (!array_key_exists($mrid, $abos)) {
					$abonnement = new MaaltijdAbonnement(($ingeschakeld ? $mrid : null), null);
					$abonnement->setVanUid($uid);
					$abonnement->setMaaltijdRepetitie($repById[$mrid]);
					$matrix[$uid][$mrid] = $abonnement;
				}
				ksort($matrix[$uid]);
			}
		}
		return $matrix;
	}

	private static function loadLedenAbonnementen($alleenNovieten = false, $alleenWaarschuwingen = false, $ingeschakeld = null, $voorLid = null) {
		$sql = 'SELECT uid, mlt_repetitie_id AS mrid,';
		if ($alleenWaarschuwingen) {
			$sql.= ' abonnement_filter,'; // controleer later
			$sql.= ' (abonneerbaar = false) AS abo_err, (lid.kring = 0) AS kring_err, (lid.status NOT IN("S_LID", "S_GASTLID", "S_NOVIET")) AS status_err,';
		}
		$sql.= ' (EXISTS ( SELECT * FROM mlt_abonnementen WHERE mlt_repetitie_id = mrid AND uid = uid )) AS abo';
		$sql.= ' FROM lid, mlt_repetities';
		$values = array();
		if ($alleenWaarschuwingen) {
			$sql.= ' HAVING abo AND (abonnement_filter != "" OR abo_err OR kring_err OR status_err)'; // niet-(kring)-leden met abo
		} elseif ($voorLid !== null) { // alles voor specifiek lid
			$sql.= ' WHERE uid = ?';
			$values[] = $voorLid;
		} elseif ($alleenNovieten) { // alles voor novieten
			$sql.= ' WHERE lid.status = "S_NOVIET"';
		} elseif ($ingeschakeld === true) {
			$sql.= ' HAVING abo = ?';
			$values[] = $ingeschakeld;
		} else { // abonneerbaar alleen voor leden
			$sql.= ' WHERE lid.status IN("S_LID", "S_GASTLID", "S_NOVIET")';
		}
		$sql.= ' ORDER BY achternaam, voornaam ASC';
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		$result = $query->fetchAll();
		return $result;
	}

	public static function getAbonnementenVoorRepetitie($mrid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new Exception('Get abonnementen voor repetitie faalt: Invalid $mrid =' . $mrid);
		}
		return self::loadAbonnementen($mrid);
	}

	public static function getAbonnementenVanNovieten() {
		$repetities = MaaltijdRepetitiesModel::getAlleRepetities();
		return self::getAbonnementenMatrix($repetities, true);
	}

	/**
	 * Laad abonnementen van een bepaalde repetitie OF voor een bepaald lid.
	 * 
	 * @param int $mrid
	 * @param String $uid
	 * @return MaaltijdAbonnement[]
	 */
	private static function loadAbonnementen($mrid = null, $uid = null) {
		$sql = 'SELECT mlt_repetitie_id, uid, wanneer_ingeschakeld';
		$sql.= ' FROM mlt_abonnementen';
		$values = array();
		if (is_int($mrid)) {
			$sql.= ' WHERE mlt_repetitie_id=?';
			$values[] = $mrid;
		} elseif ($uid !== null) {
			$sql.= ' WHERE uid=?';
			$values[] = $uid;
		}
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, 'MaaltijdAbonnement');
		return $result;
	}

	public static function inschakelenAbonnement($mrid, $uid) {
		$repetitie = MaaltijdRepetitiesModel::getRepetitie($mrid);
		if (!$repetitie->getIsAbonneerbaar()) {
			throw new Exception('Niet abonneerbaar');
		}
		if (self::getHeeftAbonnement($mrid, $uid)) {
			throw new Exception('Abonnement al ingeschakeld');
		}
		if (!MaaltijdAanmeldingenModel::checkAanmeldFilter($uid, $repetitie->getAbonnementFilter())) {
			throw new Exception('Niet toegestaan vanwege aanmeldrestrictie: ' . $repetitie->getAbonnementFilter());
		}
		$abo_aantal = self::newAbonnement($mrid, $uid);
		$abo_aantal[0]->setVanUid($uid);
		return $abo_aantal;
	}

	public static function inschakelenAbonnementVoorNovieten($mrid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new Exception('Inschakelen abonnement voor novieten faalt: Invalid $mrid =' . $mrid);
		}
		return self::newAbonnement($mrid);
	}

	/**
	 * Slaat nieuwe abonnement(en) op voor de opgegeven maaltijd-repetitie
	 * voor een specifiek lid of alle novieten (als $uid=null).
	 * En meld het lid / de novieten aan voor de komende repetitie-maaltijden.
	 * 
	 * @param int $mrid
	 * @param String $uid
	 * @return MaaltijdAbonnement OR aantal nieuwe abonnementen novieten
	 */
	private static function newAbonnement($mrid, $uid = null) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			$sql = 'INSERT IGNORE INTO mlt_abonnementen';
			$sql.= ' (mlt_repetitie_id, uid, wanneer_ingeschakeld)';
			$values = array($mrid);
			if ($uid !== null) {
				$sql.= ' VALUES (?, ?, ?)';
				$values[] = $uid;
			} else { // niet voor specifiek lid? dan voor alle novieten
				$sql.= ' SELECT ?, uid, ? FROM lid';
				$sql.= ' WHERE status = "S_NOVIET"';
			}
			$wanneer = date('Y-m-d H:i');
			$values[] = $wanneer;
			$pdo = $db->prepare($sql);
			$pdo->execute($values);
			$abos = $pdo->rowCount();
			// aanmelden voor komende repetitie-maaltijden
			if ($uid === null) { // voor de novieten
				$sql = 'SELECT uid FROM lid WHERE status = "S_NOVIET"';
				$pdo = $db->prepare($sql);
				$pdo->execute($values);
				$pdo->setFetchMode(PDO::FETCH_COLUMN, 0);
				$aantal = 0;
				foreach ($pdo as $uid) {
					try {
						$aantal += MaaltijdAanmeldingenModel::aanmeldenVoorKomendeRepetitieMaaltijden($mrid, $uid);
					} catch (Exception $e) { // niet toegestaan
						setMelding($e->getMessage(), -1);
					}
				}
				$db->commit();
				return $abos;
			} else {
				if ($abos !== 1) {
					throw new Exception('New maaltijd-abonnement faalt: $query->rowCount() =' . $abos);
				}
				$aantal = MaaltijdAanmeldingenModel::aanmeldenVoorKomendeRepetitieMaaltijden($mrid, $uid);
				$db->commit();
				return array(new MaaltijdAbonnement($mrid, $uid, $wanneer), $aantal);
			}
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

	public static function uitschakelenAbonnement($mrid, $uid) {
		if (!self::getHeeftAbonnement($mrid, $uid)) {
			throw new Exception('Abonnement al uitgeschakeld');
		}
		$aantal = self::deleteAbonnementen($mrid, $uid);
		$abo = new MaaltijdAbonnement($mrid, null);
		$abo->setVanUid($uid);
		return array($abo, $aantal);
	}

	/**
	 * Called when a MaaltijdRepetitie is being deleted.
	 * This is only possible after all MaaltijdAanmeldingen are deleted of this MaaltijdAbonnement,
	 * by deleting the Maaltijden (db foreign key door_abonnement)
	 * 
	 * @return int amount of deleted abos
	 */
	public static function verwijderAbonnementen($mrid) {
		if (!is_int($mrid) || $mrid < 0) {
			throw new Exception('Verwijder abonnementen faalt: Invalid $mrid =' . $mrid);
		}
		return self::deleteAbonnementen($mrid);
	}

	/**
	 * Called when a Lid is being made Lid-af.
	 * All linked MaaltijdAanmeldingen are deleted of this MaaltijdAbonnement.
	 * 
	 * @return int amount of deleted abos
	 */
	public static function verwijderAbonnementenVoorLid($uid) {
		$abos = self::getAbonnementenVoorLid($uid);
		$aantal = 0;
		foreach ($abos as $abo) {
			$aantal += self::deleteAbonnementen($abo->getMaaltijdRepetitieId(), $uid);
		}
		if (sizeof($abos) !== $aantal) {
			setMelding('Niet alle abonnementen zijn uitgeschakeld!', -1);
		}
		return $aantal;
	}

	private static function deleteAbonnementen($mrid, $uid = null) {
		$aantal = MaaltijdAanmeldingenModel::afmeldenDoorAbonnement($mrid, $uid);
		$sql = 'DELETE FROM mlt_abonnementen';
		$sql.= ' WHERE mlt_repetitie_id=?';
		$values = array($mrid);
		if ($uid !== null) {
			$sql.= ' AND uid=?';
			$values[] = $uid;
		}
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($uid !== null) {
			if ($query->rowCount() !== 1) {
				throw new Exception('Delete abonnementen faalt: $query->rowCount() =' . $query->rowCount());
			}
			return $aantal;
		}
		return $query->rowCount();
	}

}

?>