<?php
namespace Taken\MLT;

require_once 'taken/model/entity/MaaltijdAbonnement.class.php';
require_once 'taken/model/AanmeldingenModel.class.php';

/**
 * AbonnementenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class AbonnementenModel {

	/**
	 * Geeft de ingeschakelde abonnementen voor een lid terug plus
	 * de abonnementen die nog kunnen worden ingeschakeld op basis
	 * van de meegegeven maaltijdrepetities.
	 * 
	 * @param Lid $lid
	 * @param MaaltijdRepetitie[] $repetities
	 * @return MaaltijdAbonnement[]
	 */
	public static function getAbonnementenVoorLid($lid, $repetities) {
		if (is_string($lid)) {
			$lid = \LidCache::getLid($lid);
		}
		if (!$lid instanceof \Lid) {
			throw new \Exception('Lid bestaat niet: $lid ='. $lid);
		}
		$repById = array();
		foreach ($repetities as $repetitie) { // group by mrid
			$repById[$repetitie->getMaaltijdRepetitieId()] = $repetitie;
		}
		$lijst = array();
		$abos = self::loadAbonnementen(null, $lid->getUid());
		foreach ($abos as $abo) { // ingeschakelde abonnementen
			$mrid = $abo->getMaaltijdRepetitieId();
			if (array_key_exists($mrid, $repById)) { // weergeven
				$abo->setMaaltijdRepetitie($repById[$mrid]);
				$abo->setLid($lid);
				$lijst[$mrid] = $abo;
			}
		}
		foreach ($repById as $repetitie) {
			$mrid = $repetitie->getMaaltijdRepetitieId();
			if (!array_key_exists($mrid, $lijst)) { // uitgeschakelde abonnementen
				$abo = new MaaltijdAbonnement($repetitie->getMaaltijdRepetitieId(), null);
				$abo->setMaaltijdRepetitie($repetitie);
				$abo->setLid($lid);
				$lijst[$mrid] = $abo;
			}
		}
		ksort($lijst);
		return $lijst;
	}
	
	public static function getHeeftAbonnement($mrid, $uid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new \Exception('Get heeft abonnement faalt: Invalid $mrid ='. $mrid);
		}
		if (!\Lid::exists($uid)) {
			throw new \Exception('Lid bestaat niet: $uid ='. $uid);
		}
		$sql = 'SELECT EXISTS (SELECT * FROM mlt_abonnementen WHERE mlt_repetitie_id=? AND lid_id=?)';
		$values = array($mrid, $uid);
		$query = \CsrPdo::instance()->prepare($sql, $values);
		$query->execute($values);
		$result = $query->fetchColumn();
		return $result;
	}
	
	/**
	 * Bouwt matrix voor alle repetities en abonnementen van alle leden
	 * 
	 * @return MaaltijdAbonnement[uid][mrid]
	 */
	public static function getAbonnementenMatrix($repetities, $alleenNovieten=false, $alleenWaarschuwingen=false, $ingeschakeld=null, $voorLid=null) {
		if ($voorLid !== null && !\Lid::exists($voorLid)) {
			throw new \Exception('Lid bestaat niet: $voorLid ='. $voorLid);
		}
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
			}
			else { // uitgeschakelde abonnementen
				$abonnement = new MaaltijdAbonnement($mrid, null);
			}
			$lid = \LidCache::getLid($uid);
			$abonnement->setLid($lid);
			$abonnement->setMaaltijdRepetitie($repById[$mrid]);
			if ($alleenWaarschuwingen) {
				if ($abo['abo_err']) {
					$abonnement->setWaarschuwing('Niet abonneerbaar');
				}
				elseif ($abo['status_err']) {
					$abonnement->setWaarschuwing('Geen huidig lid');
				}
				elseif ($abo['kring_err']) {
					$abonnement->setWaarschuwing('Geen actief kringlid');
				}
				elseif (!AanmeldingenModel::checkAanmeldFilter($lid, $abo['abonnement_filter'])) {
					$abonnement->setWaarschuwing('Niet toegestaan vanwege aanmeldrestrictie: '. $abo['abonnement_filter']);
				}
				else {
					continue;;
				}
			}
			$matrix[$uid][$mrid] = $abonnement;
		}
		foreach ($repById as $mrid => $repetitie) { // vul gaten in matrix vanwege uitgeschakelde abonnementen
			foreach ($matrix as $uid => $abos) {
				if (!array_key_exists($mrid, $abos)) {
					$abonnement = new MaaltijdAbonnement(($ingeschakeld ? $mrid : null), null);
					$abonnement->setLid(\LidCache::getLid($uid));
					$abonnement->setMaaltijdRepetitie($repById[$mrid]);
					$matrix[$uid][$mrid] = $abonnement;
				}
				ksort($matrix[$uid]);
			}
		}
		return $matrix;
	}
	
	private static function loadLedenAbonnementen($alleenNovieten=false, $alleenWaarschuwingen=false, $ingeschakeld=null, $voorLid=null) {
		$sql = 'SELECT uid, mlt_repetitie_id AS mrid,';
		if ($alleenWaarschuwingen) {
			$sql.= ' abonnement_filter,'; // controleer later
			$sql.= ' (abonneerbaar = false) AS abo_err, (lid.kring = 0) AS kring_err, (lid.status NOT IN("S_LID", "S_GASTLID", "S_NOVIET")) AS status_err,';
		}
		$sql.= ' (EXISTS ( SELECT * FROM mlt_abonnementen WHERE mlt_repetitie_id = mrid AND lid_id = uid )) AS abo';
		$sql.= ' FROM lid, mlt_repetities';
		$values = array();
		if ($alleenWaarschuwingen) {
			$sql.= ' HAVING abo AND (abonnement_filter != "" OR abo_err OR kring_err OR status_err)'; // niet-(kring)-leden met abo
		}
		elseif ($voorLid !== null) { // alles voor specifiek lid
			$sql.= ' WHERE uid = ?';
			$values[] = $voorLid;
		}
		elseif ($alleenNovieten) { // alles voor novieten
			$sql.= ' WHERE lid.status = "S_NOVIET"';
		}
		elseif ($ingeschakeld === true) {
			$sql.= ' HAVING abo = ?';
			$values[] = $ingeschakeld;
		}
		else { // abonneerbaar alleen voor leden
			$sql.= ' WHERE lid.status IN("S_LID", "S_GASTLID", "S_NOVIET")';
		}
		$sql.= ' ORDER BY achternaam, voornaam ASC';
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		$result = $query->fetchAll();
		return $result;
	}
	
	public static function getAbonnementenVoorRepetitie($mrid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new \Exception('Get abonnementen voor repetitie faalt: Invalid $mrid ='. $mrid);
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
	private static function loadAbonnementen($mrid=null, $uid=null) {
		$sql = 'SELECT mlt_repetitie_id, lid_id';
		$sql.= ' FROM mlt_abonnementen';
		$values = array();
		if (is_int($mrid)) {
			$sql.= ' WHERE mlt_repetitie_id=?';
			$values[] = $mrid;
		}
		elseif ($uid !== null) {
			$sql.= ' WHERE lid_id=?';
			$values[] = $uid;
		}
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\Taken\MLT\MaaltijdAbonnement');
		return $result;
	}
	
	public static function inschakelenAbonnement($mrid, $uid) {
		$repetitie = MaaltijdRepetitiesModel::getRepetitie($mrid);
		if (!$repetitie->getIsAbonneerbaar()) {
			throw new \Exception('Niet abonneerbaar');
		}
		if (self::getHeeftAbonnement($mrid, $uid)) {
			throw new \Exception('Abonnement al ingeschakeld');
		}
		$lid = \LidCache::getLid($uid);
		if (!AanmeldingenModel::checkAanmeldFilter($lid, $repetitie->getAbonnementFilter())) {
			throw new \Exception('Niet toegestaan vanwege aanmeldrestrictie: '. $repetitie->getAbonnementFilter());
		}
		$abo = self::newAbonnement($mrid, $lid->getUid());
		$abo->setLid($lid);
		return $abo;
	}
	
	public static function inschakelenAbonnementVoorNovieten($mrid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new \Exception('Inschakelen abonnement voor novieten faalt: Invalid $mrid ='. $mrid);
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
	private static function newAbonnement($mrid, $uid=null) {
		$db = \CsrPdo::instance();
		try {
			$db->beginTransaction();
			$sql = 'INSERT IGNORE INTO mlt_abonnementen';
			$sql.= ' (mlt_repetitie_id, lid_id)';
			$values = array($mrid);
			if ($uid !== null) {
				$sql.= ' VALUES (?, ?)';
				$values[] = $uid;
			}
			else { // niet voor specifiek lid? dan voor alle novieten
				$sql.= ' SELECT ?, uid FROM lid';
				$sql.= ' WHERE status = "S_NOVIET"';
			}
			$query = $db->prepare($sql, $values);
			$query->execute($values);
			$abos = $query->rowCount();
			// aanmelden voor komende repetitie-maaltijden
			if ($uid === null) { // voor de novieten
				$sql = 'SELECT uid FROM lid WHERE status = "S_NOVIET"';
				$query = $db->prepare($sql, $values);
				$query->execute($values);
				$result = $query->fetchAll(\PDO::FETCH_COLUMN, 0);
				foreach ($result as $uid) {
					$aantal = AanmeldingenModel::aanmeldenVoorKomendeRepetitieMaaltijden($mrid, $uid);
				}
				$db->commit();
				return $abos;
			}
			else {
				if ($abos !== 1) {
					throw new \Exception('New maaltijd-abonnement faalt: $query->rowCount() ='. $abos);
				}
				$aantal = AanmeldingenModel::aanmeldenVoorKomendeRepetitieMaaltijden($mrid, $uid);
				$db->commit();
				return new MaaltijdAbonnement($mrid, $uid);
			}
		}
		catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}
	
	public static function uitschakelenAbonnement($mrid, $uid) {
		if (!self::getHeeftAbonnement($mrid, $uid)) {
			throw new \Exception('Abonnement al uitgeschakeld');
		}
		self::deleteAbonnementen($mrid, $uid);
		$abo = new MaaltijdAbonnement($mrid, null);
		$abo->setLid(\LidCache::getLid($uid));
		return $abo;
	}
	
	/**
	 * Called when a MaaltijdRepetitie is being deleted.
	 * This is only possible after all MaaltijdAanmeldingen are deleted of this MaaltijdAbonnement,
	 * by deleting the Maaltijden (db foreign key door_abonnement)
	 * 
	 * @return boolean success
	 */
	public static function verwijderAbonnementen($mrid) {
		if (!is_int($mrid) || $mrid < 0) {
			throw new \Exception('Verwijder abonnementen faalt: Invalid $mrid ='. $mrid);
		}
		return self::deleteAbonnementen($mrid);
	}
	
	private static function deleteAbonnementen($mrid, $uid=null) {
		// afmelden bij maaltijden waarbij dit abonnement de aanmelding heeft gedaan
		$maaltijden = MaaltijdenModel::getKomendeOpenRepetitieMaaltijden($mrid);
		if (!empty($maaltijden)) {
			$aantal = AanmeldingenModel::afmeldenDoorAbonnement($maaltijden, $uid);
		}
		$sql = 'DELETE FROM mlt_abonnementen';
		$sql.= ' WHERE mlt_repetitie_id=?';
		$values = array($mrid);
		if ($uid !== null) {
			$sql.= ' AND lid_id=?';
			$values[] = $uid;
		}
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($uid !== null && $query->rowCount() !== 1) {
			throw new \Exception('Delete abonnementen faalt: $query->rowCount() ='. $query->rowCount());
		}
		return $query->rowCount();
	}
}

?>