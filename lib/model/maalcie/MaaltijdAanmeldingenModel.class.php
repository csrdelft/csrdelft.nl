<?php

require_once 'model/entity/maalcie/MaaltijdAanmelding.class.php';
require_once 'model/maalcie/MaaltijdenModel.class.php';

/**
 * MaaltijdAanmeldingenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MaaltijdAanmeldingenModel {

	public static function aanmeldenVoorMaaltijd($mid, $uid, $doorUid, $aantalGasten = 0, $beheer = false, $gastenEetwens = '') {
		$maaltijd = MaaltijdenModel::getMaaltijd($mid);
		if (!$maaltijd->getIsGesloten() && $maaltijd->getBeginMoment() < strtotime(date('Y-m-d H:i'))) {
			MaaltijdenModel::sluitMaaltijd($maaltijd);
		}
		if (!$beheer) {
			if (!self::checkAanmeldFilter($uid, $maaltijd->getAanmeldFilter())) {
				throw new Exception('Niet toegestaan vanwege aanmeldrestrictie: ' . $maaltijd->getAanmeldFilter());
			}
			if ($maaltijd->getIsGesloten()) {
				throw new Exception('Maaltijd is gesloten');
			}
			if ($maaltijd->getAantalAanmeldingen() >= $maaltijd->getAanmeldLimiet()) {
				throw new Exception('Maaltijd zit al vol');
			}
		}
		if (self::getIsAangemeld($mid, $uid)) {
			if (!$beheer) {
				throw new Exception('Al aangemeld');
			}
			// aanmelding van lid updaten met aantal gasen door beheerder
			$aanmelding = self::loadAanmelding($mid, $uid);
			$verschil = $aantalGasten - $aanmelding->getAantalGasten();
			if ($verschil === 0) {
				throw new Exception('Al aangemeld met ' . $aantalGasten . ' gasten');
			}
			$aanmelding->setAantalGasten($aantalGasten);
			$aanmelding->setLaatstGewijzigd(date('Y-m-d H:i'));
			self::updateAanmelding($aanmelding);
			$maaltijd->setAantalAanmeldingen($maaltijd->getAantalAanmeldingen() + $verschil);
		} else {
			$aanmelding = self::newAanmelding($mid, $uid, $aantalGasten, $gastenEetwens, null, $doorUid);
			$maaltijd->setAantalAanmeldingen($maaltijd->getAantalAanmeldingen() + 1 + $aantalGasten);
		}
		$aanmelding->setMaaltijd($maaltijd);
		return $aanmelding;
	}

	public static function aanmeldenDoorAbonnement($mid, $mrid, $uid) {
		return self::newAanmelding($mid, $uid, 0, '', $mrid, null);
	}

	/**
	 * Called when a MaaltijdAbonnement is being deleted (turned off) or a MaaltijdRepetitie is being deleted.
	 * 
	 * @param int $mrid id van de betreffede MaaltijdRepetitie
	 * @param type $uid Lid voor wie het MaaltijdAbonnement wordt uitschakeld
	 */
	public static function afmeldenDoorAbonnement($mrid, $uid = null) {
		// afmelden bij maaltijden waarbij dit abonnement de aanmelding heeft gedaan
		$maaltijden = MaaltijdenModel::getKomendeOpenRepetitieMaaltijden($mrid);
		if (empty($maaltijden)) {
			return;
		}
		$byMid = array();
		foreach ($maaltijden as $maaltijd) {
			if (!$maaltijd->getIsGesloten() && !$maaltijd->getIsVerwijderd()) {
				$byMid[$maaltijd->getMaaltijdId()] = $maaltijd;
			}
		}
		$aanmeldingen = self::getAanmeldingenVoorLid($byMid, $uid);
		$aantal = 0;
		foreach ($aanmeldingen as $mid => $aanmelding) {
			if ($mrid === $aanmelding->getDoorAbonnement()) {
				self::deleteAanmeldingen($mid, $uid);
				$aantal++;
			}
		}
		return $aantal;
	}

	public static function afmeldenDoorLid($mid, $uid, $beheer = false) {
		if (!self::getIsAangemeld($mid, $uid)) {
			throw new Exception('Niet aangemeld');
		}
		$maaltijd = MaaltijdenModel::getMaaltijd($mid);
		if (!$maaltijd->getIsGesloten() && $maaltijd->getBeginMoment() < time()) {
			MaaltijdenModel::sluitMaaltijd($maaltijd);
		}
		if (!$beheer && $maaltijd->getIsGesloten()) {
			throw new Exception('Maaltijd is gesloten');
		}
		$aanmelding = self::loadAanmelding($mid, $uid);
		self::deleteAanmeldingen($mid, $uid);
		$maaltijd->setAantalAanmeldingen($maaltijd->getAantalAanmeldingen() - 1 - $aanmelding->getAantalGasten());
		return $maaltijd;
	}

	public static function saveGasten($mid, $uid, $gasten) {
		if (!is_int($mid) || $mid <= 0) {
			throw new Exception('Save gasten faalt: Invalid $mid =' . $mid);
		}
		if (!is_int($gasten) || $gasten < 0) {
			throw new Exception('Save gasten faalt: Invalid $gasten =' . $gasten);
		}
		if (!self::getIsAangemeld($mid, $uid)) {
			throw new Exception('Niet aangemeld');
		}
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			$maaltijd = MaaltijdenModel::getMaaltijd($mid);
			if ($maaltijd->getIsGesloten()) {
				throw new Exception('Maaltijd is gesloten');
			}
			$aanmelding = self::loadAanmelding($mid, $uid);
			$verschil = $gasten - $aanmelding->getAantalGasten();
			if ($maaltijd->getAantalAanmeldingen() + $verschil > $maaltijd->getAanmeldLimiet()) {
				throw new Exception('Maaltijd zit te vol');
			}
			if ($aanmelding->getAantalGasten() !== $gasten) {
				$aanmelding->setLaatstGewijzigd(date('Y-m-d H:i'));
			}
			$aanmelding->setAantalGasten($gasten);
			self::updateAanmelding($aanmelding);
			$maaltijd->setAantalAanmeldingen($maaltijd->getAantalAanmeldingen() + $verschil);
			$aanmelding->setMaaltijd($maaltijd);
			$db->commit();
			return $aanmelding;
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

	public static function saveGastenEetwens($mid, $uid, $opmerking) {
		if (!is_int($mid) || $mid <= 0) {
			throw new Exception('Save gasten eetwens faalt: Invalid $mid =' . $mid);
		}
		if (!self::getIsAangemeld($mid, $uid)) {
			throw new Exception('Niet aangemeld');
		}
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			$maaltijd = MaaltijdenModel::getMaaltijd($mid);
			if ($maaltijd->getIsGesloten()) {
				throw new Exception('Maaltijd is gesloten');
			}
			$aanmelding = self::loadAanmelding($mid, $uid);
			if ($aanmelding->getAantalGasten() <= 0) {
				throw new Exception('Geen gasten aangemeld');
			}
			$aanmelding->setMaaltijd($maaltijd);
			$aanmelding->setGastenEetwens($opmerking);
			self::updateAanmelding($aanmelding);
			$db->commit();
			return $aanmelding;
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

	public static function getAanmeldingenVoorMaaltijd(Maaltijd $maaltijd) {
		$aanmeldingen = self::loadAanmeldingen(array($maaltijd->getMaaltijdId()));
		$lijst = array();
		foreach ($aanmeldingen as $aanmelding) {
			$aanmelding->setMaaltijd($maaltijd);
			$naam = ProfielModel::getNaam($aanmelding->getUid(), 'streeplijst');
			$lijst[$naam] = $aanmelding;
			for ($i = $aanmelding->getAantalGasten(); $i > 0; $i--) {
				$gast = new MaaltijdAanmelding();
				$gast->setDoorUid($aanmelding->getUid());
				$lijst[$naam . 'gast' . $i] = $gast;
			}
		}
		ksort($lijst);
		return $lijst;
	}

	public static function getRecenteAanmeldingenVoorLid($uid, $timestamp) {
		$maaltijdenById = MaaltijdenModel::getRecenteMaaltijden($timestamp);
		return MaaltijdAanmeldingenModel::getAanmeldingenVoorLid($maaltijdenById, $uid);
	}

	public static function getAanmeldingenVoorLid($maaltijdenById, $uid) {
		if (empty($maaltijdenById)) {
			return $maaltijdenById; // array()
		}
		$aanmeldingen = self::loadAanmeldingen(array_keys($maaltijdenById), $uid);
		$result = array();
		foreach ($aanmeldingen as $aanmelding) {
			$aanmelding->setMaaltijd($maaltijdenById[$aanmelding->getMaaltijdId()]);
			$result[$aanmelding->getMaaltijdId()] = $aanmelding;
		}
		return $result;
	}

	public static function getIsAangemeld($mid, $uid, $doorAbo = null) {
		if (!is_int($mid) || $mid <= 0) {
			throw new Exception('Load maaltijd faalt: Invalid $mid =' . $mid);
		}
		$sql = 'SELECT EXISTS (SELECT * FROM mlt_aanmeldingen WHERE maaltijd_id=? AND uid=?';
		$values = array($mid, $uid);
		if ($doorAbo !== null) {
			$sql.= ' AND door_abonnement=?';
			$values[] = $doorAbo;
		}
		$sql.= ')';
		$query = \Database::instance()->prepare($sql);
		$query->execute($values);
		$result = $query->fetchColumn();
		return (boolean) $result;
	}

	private static function loadAanmelding($mid, $uid) {
		$aanmeldingen = self::loadAanmeldingen(array($mid), $uid, 1);
		if (!array_key_exists(0, $aanmeldingen)) {
			throw new Exception('Load aanmelding faalt: Not found $mid =' . $mid);
		}
		return $aanmeldingen[0];
	}

	/**
	 * @param array $mids
	 * @param null $uid
	 * @param null $limit
	 * @return MaaltijdAanmelding[]
	 */
	private static function loadAanmeldingen(array $mids, $uid = null, $limit = null) {
		$sql = 'SELECT maaltijd_id, uid, aantal_gasten, gasten_eetwens, door_abonnement, door_uid, laatst_gewijzigd';
		$sql.= ' FROM mlt_aanmeldingen';
		$sql.= ' WHERE (maaltijd_id=?';
		for ($i = sizeof($mids); $i > 1; $i--) {
			$sql.= ' OR maaltijd_id=?';
		}
		$sql.= ')';
		$values = $mids;
		if ($uid !== null) {
			$sql.= ' AND uid=?';
			$values[] = $uid;
		}
		if (is_int($limit)) {
			$sql.= ' LIMIT ' . $limit;
		}
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, 'MaaltijdAanmelding');
		return $result;
	}

	private static function newAanmelding($mid, $uid, $gasten, $opmerking, $doorAbo, $doorUid) {
		$sql = 'INSERT IGNORE INTO mlt_aanmeldingen';
		$sql.= ' (maaltijd_id, uid, aantal_gasten, gasten_eetwens, door_abonnement, door_uid, laatst_gewijzigd)';
		$wanneer = date('Y-m-d H:i');
		if ($mid === null) { // niet voor specifieke maaltijd? dan voor alle komende repetitie-maaltijden
			$sql.= ' SELECT maaltijd_id, ?, ?, ?, ?, ?, ? FROM mlt_maaltijden';
			$sql.= ' WHERE mlt_repetitie_id = ? AND gesloten = FALSE AND verwijderd = FALSE AND datum >= ?';
			$values = array($uid, $gasten, $opmerking, $doorAbo, $doorUid, $wanneer, $doorAbo, date('Y-m-d'));
		} else {
			$sql.= ' VALUES (?, ?, ?, ?, ?, ?, ?)';
			$values = array($mid, $uid, $gasten, $opmerking, $doorAbo, $doorUid, $wanneer);
		}
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($mid !== null) {
			if ($query->rowCount() !== 1) {
				throw new Exception('New aanmelding faalt: $query->rowCount() =' . $query->rowCount());
			}
			return new MaaltijdAanmelding($mid, $uid, $gasten, $opmerking, $doorAbo, $doorUid, $wanneer);
		}
		return $query->rowCount();
	}

	/**
	 * Called when a Maaltijd is being deleted.
	 * 
	 * @param int $mid maaltijd-id
	 */
	public static function deleteAanmeldingenVoorMaaltijd($mid) {
		self::deleteAanmeldingen($mid);
	}

	private static function deleteAanmeldingen($mid, $uid = null) {
		$sql = 'DELETE FROM mlt_aanmeldingen';
		$sql.= ' WHERE maaltijd_id=?';
		$values = array($mid);
		if ($uid !== null) {
			$sql.= ' AND uid=?';
			$values[] = $uid;
		}
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($uid !== null && $query->rowCount() !== 1) {
			throw new Exception('Delete aanmelding faalt: $query->rowCount() =' . $query->rowCount());
		}

		return 1;
	}

	private static function updateAanmelding(MaaltijdAanmelding $aanmelding) {
		$sql = 'UPDATE mlt_aanmeldingen';
		$sql.= ' SET aantal_gasten=?, gasten_eetwens=?, door_abonnement=?, door_uid=?, laatst_gewijzigd=?';
		$sql.= ' WHERE maaltijd_id=? AND uid=?';
		$values = array(
			$aanmelding->getAantalGasten(),
			$aanmelding->getGastenEetwens(),
			$aanmelding->getDoorAbonnement(),
			$aanmelding->getDoorUid(),
			$aanmelding->getLaatstGewijzigd(),
			$aanmelding->getMaaltijdId(),
			$aanmelding->getUid()
		);
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new Exception('Update aanmelding faalt: $query->rowCount() =' . $query->rowCount());
		}
	}

	/**
	 * Controleer of alle aanmeldingen voor de maaltijden nog in overeenstemming zijn met het aanmeldfilter.
	 *
	 * @param Maaltijd[] $maaltijden
	 * @return int|void
	 */
	public static function checkAanmeldingenFilter($filter, array $maaltijden) {
		$mids = array();
		foreach ($maaltijden as $maaltijd) {
			if (!$maaltijd->getIsGesloten() && !$maaltijd->getIsVerwijderd()) {
				$mids[] = $maaltijd->getMaaltijdId();
			}
		}
		if (empty($mids)) {
			return 0;
		}
		$aantal = 0;
		$aanmeldingen = self::loadAanmeldingen($mids);
		foreach ($aanmeldingen as $aanmelding) { // check filter voor elk aangemeld lid
			$uid = $aanmelding->getUid();
			if (!self::checkAanmeldFilter($uid, $filter)) { // verwijder aanmelding indien niet toegestaan
				$aantal += self::deleteAanmeldingen($aanmelding->getMaaltijdId(), $uid);
			}
		}
		return $aantal;
	}

	public static function checkAanmeldFilter($uid, $filter) {
		$account = AccountModel::get($uid); // false if account does not exist
		if (!$account) {
			throw new Exception('Lid bestaat niet: $uid =' . $uid);
		}
		if (empty($filter)) {
			return true;
		}
		return AccessModel::mag($account, $filter);
	}

	// Repetitie-Maaltijden ############################################################

	/**
	 * Alleen aanroepen voor inschakelen abonnement!
	 * 
	 * @param int $mrid
	 * @param string $uid
	 * @return int|false aantal aanmeldingen or false
	 * @throws Exception indien niet toegestaan vanwege aanmeldrestrictie
	 */
	public static function aanmeldenVoorKomendeRepetitieMaaltijden($mrid, $uid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new Exception('Invalid abonnement: $voorAbo =' . $mrid);
		}
		$repetitie = MaaltijdRepetitiesModel::getRepetitie($mrid);
		if (!self::checkAanmeldFilter($uid, $repetitie->getAbonnementFilter())) {
			throw new Exception('Niet toegestaan vanwege aanmeldrestrictie: ' . $repetitie->getAbonnementFilter());
		}
		return self::newAanmelding(null, $uid, 0, '', $mrid, null);
	}

}
