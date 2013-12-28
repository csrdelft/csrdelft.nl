<?php
namespace Taken\MLT;

require_once 'taken/model/entity/MaaltijdAanmelding.class.php';

/**
 * AanmeldingenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class AanmeldingenModel {

	public static function aanmeldenVoorMaaltijd($mid, $uid, $doorUid, $aantalGasten=0, $beheer=false, $gastenOpmerking='') {
		$maaltijd = MaaltijdenModel::getMaaltijd($mid);
		if (!$maaltijd->getIsGesloten() && $maaltijd->getBeginMoment() < strtotime(date('Y-m-d H:i'))) {
			MaaltijdenModel::sluitMaaltijd($maaltijd);
		}
		if (!$beheer) {
			if (!self::checkAanmeldFilter($uid, $maaltijd->getAanmeldFilter())) {
				throw new \Exception('Niet toegestaan vanwege aanmeldrestrictie: '. $maaltijd->getAanmeldFilter());
			}
			if ($maaltijd->getIsGesloten()) {
				throw new \Exception('Maaltijd is gesloten');
			}
			if ($maaltijd->getAantalAanmeldingen() >= $maaltijd->getAanmeldLimiet()) {
				throw new \Exception('Maaltijd zit al vol');
			}
		}
		if (self::getIsAangemeld($mid, $uid)) {
			if (!$beheer) {
				throw new \Exception('Al aangemeld');
			}
			// aanmelding van lid updaten met aantal gasen door beheerder
			$aanmelding = self::loadAanmelding($mid, $uid);
			$verschil = $aantalGasten - $aanmelding->getAantalGasten();
			if ($verschil === 0) {
				throw new \Exception('Al aangemeld met '. $aantalGasten .' gasten');
			}
			$aanmelding->setAantalGasten($aantalGasten);
			$aanmelding->setLaatstGewijzigd(date('Y-m-d H:i'));
			self::updateAanmelding($aanmelding);
			$maaltijd->setAantalAanmeldingen($maaltijd->getAantalAanmeldingen() + $verschil);
		}
		else {
			$aanmelding = self::newAanmelding($mid, $uid, $aantalGasten, $gastenOpmerking, null, $doorUid);
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
	public static function afmeldenDoorAbonnement($mrid, $uid=null) {
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
	
	public static function afmeldenDoorLid($mid, $uid, $beheer=false) {
		if (!self::getIsAangemeld($mid, $uid)) {
			throw new \Exception('Niet aangemeld');
		}
		$maaltijd = MaaltijdenModel::getMaaltijd($mid);
		if (!$maaltijd->getIsGesloten() && $maaltijd->getBeginMoment() < time()) {
			MaaltijdenModel::sluitMaaltijd($maaltijd);
		}
		if (!$beheer && $maaltijd->getIsGesloten()) {
			throw new \Exception('Maaltijd is gesloten');
		}
		$aanmelding = self::loadAanmelding($mid, $uid);
		self::deleteAanmeldingen($mid, $uid);
		$maaltijd->setAantalAanmeldingen($maaltijd->getAantalAanmeldingen() - 1 - $aanmelding->getAantalGasten());
		return $maaltijd;
	}
	
	public static function saveGasten($mid, $uid, $gasten) {
		if (!is_int($mid) || $mid <= 0) {
			throw new \Exception('Save gasten faalt: Invalid $mid ='. $mid);
		}
		if (!is_int($gasten) || $gasten < 0) {
			throw new \Exception('Save gasten faalt: Invalid $gasten ='. $gasten);
		}
		if (!self::getIsAangemeld($mid, $uid)) {
			throw new \Exception('Niet aangemeld');
		}
		$db = \CsrPdo::instance();
		try {
			$db->beginTransaction();
			$maaltijd = MaaltijdenModel::getMaaltijd($mid);
			if ($maaltijd->getIsGesloten()) {
				throw new \Exception('Maaltijd is gesloten');
			}
			$aanmelding = self::loadAanmelding($mid, $uid);
			$verschil = $gasten - $aanmelding->getAantalGasten();
			if ($maaltijd->getAantalAanmeldingen() + $verschil > $maaltijd->getAanmeldLimiet()) {
				throw new \Exception('Maaltijd zit te vol');
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
		}
		catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

	public static function saveGastenOpmerking($mid, $uid, $opmerking) {
		if (!is_int($mid) || $mid <= 0) {
			throw new \Exception('Save gasten-opmerking faalt: Invalid $mid ='. $mid);
		}
		if (!self::getIsAangemeld($mid, $uid)) {
			throw new \Exception('Niet aangemeld');
		}
		$db = \CsrPdo::instance();
		try {
			$db->beginTransaction();
			$maaltijd = MaaltijdenModel::getMaaltijd($mid);
			if ($maaltijd->getIsGesloten()) {
				throw new \Exception('Maaltijd is gesloten');
			}
			$aanmelding = self::loadAanmelding($mid, $uid);
			$aanmelding->setMaaltijd($maaltijd);
			$aanmelding->setGastenOpmerking($opmerking);
			self::updateAanmelding($aanmelding);
			$db->commit();
			return $aanmelding;
		}
		catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}
	
	public static function getAanmeldingenVoorMaaltijd(Maaltijd $maaltijd) {
		$aanmeldingen = self::loadAanmeldingen(array($maaltijd->getMaaltijdId()));
		$lijst = array();
		foreach ($aanmeldingen as $aanmelding) {
			$aanmelding->setMaaltijd($maaltijd);
			$naam = $aanmelding->getLid()->getNaamLink('streeplijst');
			$lijst[$naam] = $aanmelding;
			for ($i = $aanmelding->getAantalGasten(); $i > 0; $i--) {
				$gast = new MaaltijdAanmelding();
				$gast->setDoorLidId($aanmelding->getLidId());
				$lijst[$naam .'gast'. $i] = $gast;
			}
		}
		ksort($lijst);
		return $lijst;
	}
	
	public static function getRecenteAanmeldingenVoorLid($uid) {
		$maaltijdenById = MaaltijdenModel::getRecenteMaaltijden();
		return AanmeldingenModel::getAanmeldingenVoorLid($maaltijdenById, $uid);
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
	
	public static function getIsAangemeld($mid, $uid, $doorAbo=null) {
		if (!is_int($mid) || $mid <= 0) {
			throw new \Exception('Load maaltijd faalt: Invalid $mid ='. $mid);
		}
		$sql = 'SELECT EXISTS (SELECT * FROM mlt_aanmeldingen WHERE maaltijd_id=? AND lid_id=?';
		$values = array($mid, $uid);
		if ($doorAbo !== null) {
			$sql.= ' AND door_abonnement=?';
			$values[] = $doorAbo;
		}
		$sql.= ')';
		$query = \CsrPdo::instance()->prepare($sql, $values);
		$query->execute($values);
		$result = $query->fetchColumn();
		return (boolean) $result;
	}
	
	private static function loadAanmelding($mid, $uid) {
		$aanmeldingen = self::loadAanmeldingen(array($mid), $uid, 1);
		if (!array_key_exists(0, $aanmeldingen)) {
			throw new \Exception('Load aanmelding faalt: Not found $mid ='. $mid);
		}
		return $aanmeldingen[0];
	}
	
	private static function loadAanmeldingen(array $mids, $uid=null, $limit=null) {
		$sql = 'SELECT maaltijd_id, lid_id, aantal_gasten, gasten_opmerking, door_abonnement, door_lid_id, laatst_gewijzigd';
		$sql.= ' FROM mlt_aanmeldingen';
		$sql.= ' WHERE (maaltijd_id=?';
		for ($i = sizeof($mids); $i > 1; $i--) {
			$sql.= ' OR maaltijd_id=?';
		}
		$sql.= ')';
		$values = $mids;
		if ($uid !== null) {
			$sql.= ' AND lid_id=?';
			$values[] = $uid;
		}
		if (is_int($limit)) {
			$sql.= ' LIMIT '. $limit;
		}
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\Taken\MLT\MaaltijdAanmelding');
		return $result;
	}
	
	private static function newAanmelding($mid, $uid, $gasten, $opmerking, $doorAbo, $doorUid) {
		$sql = 'INSERT IGNORE INTO mlt_aanmeldingen';
		$sql.= ' (maaltijd_id, lid_id, aantal_gasten, gasten_opmerking, door_abonnement, door_lid_id, laatst_gewijzigd)';
		$wanneer = date('Y-m-d H:i');
		if ($mid === null) { // niet voor specifieke maaltijd? dan voor alle komende repetitie-maaltijden
			$sql.= ' SELECT maaltijd_id, ?, ?, ?, ?, ?, ? FROM mlt_maaltijden';
			$sql.= ' WHERE mlt_repetitie_id = ? AND gesloten = false AND verwijderd = false AND datum >= ?';
			$values = array($uid, $gasten, $opmerking, $doorAbo, $doorUid, $wanneer, $doorAbo, date('Y-m-d'));
		}
		else {
			$sql.= ' VALUES (?, ?, ?, ?, ?, ?, ?)';
			$values = array($mid, $uid, $gasten, $opmerking, $doorAbo, $doorUid, $wanneer);
		}
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($mid !== null) {
			if ($query->rowCount() !== 1) {
				throw new \Exception('New aanmelding faalt: $query->rowCount() ='. $query->rowCount());
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
	
	private static function deleteAanmeldingen($mid, $uid=null) {
		$sql = 'DELETE FROM mlt_aanmeldingen';
		$sql.= ' WHERE maaltijd_id=?';
		$values = array($mid);
		if ($uid !== null) {
			$sql.= ' AND lid_id=?';
			$values[] = $uid;
		}
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($uid !== null && $query->rowCount() !== 1) {
			throw new \Exception('Delete aanmelding faalt: $query->rowCount() ='. $query->rowCount());
		}
	}
	
	private static function updateAanmelding(MaaltijdAanmelding $aanmelding) {
		$sql = 'UPDATE mlt_aanmeldingen';
		$sql.= ' SET aantal_gasten=?, gasten_opmerking=?, door_abonnement=?, door_lid_id=?, laatst_gewijzigd=?';
		$sql.= ' WHERE maaltijd_id=? AND lid_id=?';
		$values = array(
			$aanmelding->getAantalGasten(),
			$aanmelding->getGastenOpmerking(),
			$aanmelding->getDoorAbonnement(),
			$aanmelding->getDoorLidId(),
			$aanmelding->getLaatstGewijzigd(),
			$aanmelding->getMaaltijdId(),
			$aanmelding->getLidId()
		);
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new \Exception('Update aanmelding faalt: $query->rowCount() ='. $query->rowCount());
		}
	}
	
	/**
	 * Controleer of alle aanmeldingen voor de maaltijden nog in overeenstemming zijn met het aanmeldfilter.
	 * 
	 * @param Maaltijd[] $maaltijden
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
			$uid = $aanmelding->getLidId();
			if (!self::checkAanmeldFilter($uid, $filter)) { // verwijder aanmelding indien niet toegestaan
				$aantal += self::deleteAanmeldingen($aanmelding->getMaaltijdId(), $uid);
			}
		}
		return $aantal;
	}
	
	public static function checkAanmeldFilter($uid, $filter) {
		$lid = \LidCache::getLid($uid); // false if lid does not exist
		if (!$lid instanceof \Lid) {
			throw new \Exception('Lid bestaat niet: $uid ='. $uid);
		}
		if ($filter === '') {
			return true;
		}
		if (strpos($filter, '!') === 0) {
			return !self::checkAanmeldFilter($uid, substr($filter, 1));
		}
		
		$filter = explode(':', $filter);
		if (sizeof($filter) !== 2) {
			throw new \Exception('Check aanmeldfilter faalt');
		}
		switch ($filter[0]) {

		// Behoort een lid tot een bepaalde verticale?
		case 'verticale':

			$verticale = strtoupper($filter[1]);
			if (is_int($verticale)) {
				if ($verticale === $lid->getVerticaleID()) {
					return true;
				}
			}
			elseif ($verticale === $lid->getVerticaleLetter()) {
				return true;
			}
			elseif ($verticale === strtoupper($lid->getVerticale())) {
				return true;
			}
			return false;

		// Behoort een lid tot een bepaalde (h.t.) groep?
		// als een string als bijvoorbeeld 'pubcie' wordt meegegeven zoekt de ketzer
		// de h.t. groep met die korte naam erbij, als het getal is uiteraard de groep
		// met dat id.
		// met de toevoeging '>Fiscus' kan ook specifieke functie geëist worden binnen een groep
		case 'groep':
			require_once 'groepen/groep.class.php';

			try {
				$parts = explode('>', $filter[1], 2); // Splitst opgegeven term in groepsnaam en functie
				$groep = new \Groep($parts[0]);
				if ($groep->isLid($uid)) {
					// Wordt er een functie gevraagd?
					if (isset($parts[1])) {
						$functie = $groep->getFunctie();
						if (strtolower($functie[0]) === strtolower($parts[1])) {
							return true;
						}
					}
					else {
						return true;
					}
				}
			}
			catch (\Exception $e) { // groep bestaat niet
			}
			return false;

		// Is een lid man of vrouw?
		case 'geslacht':

			$geslacht = strtolower($filter[1]);
			if ($geslacht === $lid->getGeslacht()) {
				return true;
			}
			return false;

		// Behoort een lid tot een bepaalde lichting?
		case 'lichting':
		case 'lidjaar':

			$lidjaar = $filter[1];
			if ($lidjaar === $lid->getProperty('lidjaar')) {
				return true;
			}
			return false;

		}
		return false;
	}
	
	// Repetitie-Maaltijden ############################################################
	
	/**
	 * Alleen aanroepen voor inschakelen abonnement!
	 * 
	 * @param int $mrid
	 * @param string $uid
	 * @return aantal aanmeldingen or false
	 * @throws \Exception
	 */
	public static function aanmeldenVoorKomendeRepetitieMaaltijden($mrid, $uid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new \Exception('Invalid abonnement: $voorAbo ='. $mrid);
		}
		$repetitie = MaaltijdRepetitiesModel::getRepetitie($mrid);
		if (!self::checkAanmeldFilter($uid, $repetitie->getAbonnementFilter())) {
			throw new \Exception('Niet toegestaan vanwege aanmeldrestrictie: '. $repetitie->getAbonnementFilter());
		}
		return self::newAanmelding(null, $uid, 0, '', $mrid, null);
	}
}

?>