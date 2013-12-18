<?php
namespace Taken\MLT;

require_once 'taken/model/entity/Maaltijd.class.php';
require_once 'taken/model/CorveeRepetitiesModel.class.php';
require_once 'taken/model/AbonnementenModel.class.php';

/**
 * MaaltijdenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MaaltijdenModel {

	public static function openMaaltijd(Maaltijd $maaltijd) {
		if (!$maaltijd->getIsGesloten()) {
			throw new \Exception('Maaltijd is al geopend');
		}
		$maaltijd->setGesloten(false);
		self::updateMaaltijd($maaltijd);
		return $maaltijd;
	}
	
	public static function sluitMaaltijd(Maaltijd $maaltijd) {
		if ($maaltijd->getIsGesloten()) {
			throw new \Exception('Maaltijd is al gesloten');
		}
		$maaltijd->setGesloten(true);
		$maaltijd->setLaatstGesloten(date('Y-m-d H:i'));
		self::updateMaaltijd($maaltijd);
	}
	
	public static function getAlleMaaltijden() {
		return self::loadMaaltijden('verwijderd = false');
	}
	
	/**
	 * Haalt de maaltijden op voor het ingelode lid tussen de opgegeven data.
	 * 
	 * @param timestamp $van
	 * @param timestamp $tot
	 * @return Maaltijd[] (implements Agendeerbaar)
	 */
	public static function getMaaltijdenVoorAgenda($van, $tot) {
		if ($van === null) {
			$van = time();
		}
		elseif (!is_int($van)) {
			throw new \Exception('Invalid timestamp: $van getMaaltijdenVoorAgenda()');
		}
		if ($tot === null) {
			$tot = strtotime($GLOBALS['maaltijden_ketzer_vooraf']);
		}
		elseif (!is_int($tot)) {
			throw new \Exception('Invalid timestamp: $tot getMaaltijdenVoorAgenda()');
		}
		$maaltijden = self::loadMaaltijden('verwijderd = false AND datum >= ? AND datum <= ?', array(date('Y-m-d', $van), date('Y-m-d', $tot)));
		$maaltijden = self::filterMaaltijdenVoorLid($maaltijden, \LoginLid::instance()->getUid());
		return $maaltijden;
	}
	
	/**
	 * Haalt de maaltijden op die beschikbaar zijn voor aanmelding voor het lid in de ingestelde periode vooraf.
	 * 
	 * @param string $uid
	 * @return Maaltijd[]
	 */
	public static function getKomendeMaaltijdenVoorLid($uid) {
		$maaltijden = self::loadMaaltijden('verwijderd = false AND datum >= ? AND datum <= ?', array(date('Y-m-d'), date('Y-m-d', strtotime($GLOBALS['maaltijden_ketzer_vooraf']))));
		$maaltijden = self::filterMaaltijdenVoorLid($maaltijden, $uid);
		return $maaltijden;
	}
	
	/**
	 * Haalt de maaltijden op in de ingestelde periode achteraf.
	 * 
	 * @return Maaltijd[]
	 */
	public static function getRecenteMaaltijden() {
		$maaltijden = self::loadMaaltijden('verwijderd = false AND datum <= ?', array(date('Y-m-d', strtotime($GLOBALS['maaltijden_recent_lidprofiel']))));
		$maaltijdenById = array();
		foreach ($maaltijden as $maaltijd) {
			$maaltijdenById[$maaltijd->getMaaltijdId()] = $maaltijd;
		}
		return $maaltijdenById;
	}
	
	/**
	 * Haalt de maaltijd op die in een ketzer zal worden weergegeven.
	 * 
	 * @return Maaltijd
	 */
	public static function getMaaltijdVoorKetzer($mid) {
		$maaltijden = array(self::getMaaltijd($mid));
		$maaltijden = self::filterMaaltijdenVoorLid($maaltijden, \LoginLid::instance()->getUid());
		if (!empty($maaltijden)) {
			return reset($maaltijden);
		}
		return false;
	}
	
	public static function getVerwijderdeMaaltijden() {
		return self::loadMaaltijden('verwijderd = true');
	}
	
	public static function getMaaltijd($mid, $verwijderd=false) {
		$maaltijd = self::loadMaaltijd($mid);
		if (!$verwijderd && $maaltijd->getIsVerwijderd()) {
			throw new \Exception('Maaltijd is verwijderd');
		}
		return $maaltijd;
	}
	
	private static function loadMaaltijd($mid) {
		if (!is_int($mid) || $mid <= 0) {
			throw new \Exception('Load maaltijd faalt: Invalid $mid ='. $mid);
		}
		$maaltijden = self::loadMaaltijden('m.maaltijd_id = ?', array($mid), 1);
		if (!array_key_exists(0, $maaltijden)) {
			throw new \Exception('Load maaltijd faalt: Not found $mid ='. $mid);
		}
		return $maaltijden[0];
	}
	
	public static function saveMaaltijd($mid, $mrid, $titel, $limiet, $datum, $tijd, $prijs, $filter) {
		$db = \CsrPdo::instance();
		try {
			$db->beginTransaction();
			$verwijderd = 0;
			if ($mid === 0) {
				$maaltijd = self::newMaaltijd($mrid, $titel, $limiet, $datum, $tijd, $prijs, $filter);
			}
			else {
				$maaltijd = self::getMaaltijd($mid);
				$maaltijd->setTitel($titel);
				$maaltijd->setAanmeldLimiet($limiet);
				$maaltijd->setDatum($datum);
				$maaltijd->setTijd($tijd);
				$maaltijd->setPrijs($prijs);
				$maaltijd->setAanmeldFilter($filter);
				self::updateMaaltijd($maaltijd);
				if (!$maaltijd->getIsGesloten() && $maaltijd->getBeginMoment() < time()) {
					MaaltijdenModel::sluitMaaltijd($maaltijd);
				}
				if (!$maaltijd->getIsGesloten() && !$maaltijd->getIsVerwijderd() && !empty($filter)) {
					$verwijderd = AanmeldingenModel::checkAanmeldingenFilter($filter, array($maaltijd));
					$maaltijd->setAantalAanmeldingen($maaltijd->getAantalAanmeldingen() - $verwijderd);
				}
			}
			$db->commit();
			return array($maaltijd, $verwijderd);
		}
		catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}
	
	public static function prullenbakLeegmaken() {
		$aantal = 0;
		$maaltijden = self::getVerwijderdeMaaltijden();
		foreach ($maaltijden as $maaltijd) {
			try {
				self::verwijderMaaltijd($maaltijd->getMaaltijdId());
				$aantal++;
			}
			catch (\Exception $e) {
				setMelding($e->getMessage(), -1);
			}
		}
		return $aantal;
	}
	
	public static function verwijderMaaltijd($mid) {
		$maaltijd = self::loadMaaltijd($mid);
		if ($maaltijd->getIsVerwijderd()) {
			if (\Taken\CRV\TakenModel::existMaaltijdCorvee($mid)) {
				\Taken\CRV\TakenModel::verwijderMaaltijdCorvee($mid); // delete corveetaken first (foreign key)
				throw new \Exception('Alle bijbehorende corveetaken zijn naar de prullenbak verplaatst. Verwijder die eerst!');
			}
			self::deleteMaaltijd($mid); // definitief verwijderen
		}
		else {
			$maaltijd->setVerwijderd(true);
			self::updateMaaltijd($maaltijd);
		}
	}
	
	private static function deleteMaaltijd($mid) {
		$db = \CsrPdo::instance();
		try {
			$db->beginTransaction();
			AanmeldingenModel::deleteAanmeldingenVoorMaaltijd($mid); // delete aanmeldingen first (foreign key)
			$sql = 'DELETE FROM mlt_maaltijden';
			$sql.= ' WHERE maaltijd_id = ?';
			$values = array($mid);
			$query = $db->prepare($sql, $values);
			$query->execute($values);
			if ($query->rowCount() !== 1) {
				throw new \Exception('Delete maaltijd faalt: $query->rowCount() ='. $query->rowCount());
			}
			$db->commit();
		}
		catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}
	
	public static function herstelMaaltijd($mid) {
		$maaltijd = self::loadMaaltijd($mid);
		if (!$maaltijd->getIsVerwijderd()) {
			throw new \Exception('Maaltijd is niet verwijderd');
		}
		$maaltijd->setVerwijderd(false);
		self::updateMaaltijd($maaltijd);
		return $maaltijd;
	}
	
	/**
	 * Filtert de maaltijden met het aanmeld-filter van de maaltijd op de permissies van het lid.
	 * 
	 * @param Maaltijd[] $maaltijden
	 * @param string $uid
	 * @return Maaltijd[]
	 */
	private static function filterMaaltijdenVoorLid($maaltijden, $uid) {
		$result = array();
		foreach ($maaltijden as $maaltijd) {
			if (AanmeldingenModel::checkAanmeldFilter($uid, $maaltijd->getAanmeldFilter())) {
				$result[$maaltijd->getMaaltijdId()] = $maaltijd;
			}
		}
		return $result;
	}
	
	private static function loadMaaltijden($where=null, $values=array(), $limit=null) {
		$sql = 'SELECT m.maaltijd_id, mlt_repetitie_id, titel, aanmeld_limiet, datum, tijd, prijs, gesloten, laatst_gesloten, verwijderd, aanmeld_filter, COUNT(a.lid_id) + SUM(IFNULL(aantal_gasten, 0)) AS aantal_aanmeldingen';
		$sql.= ' FROM mlt_maaltijden m';
		$sql.= ' LEFT JOIN mlt_aanmeldingen a ON m.maaltijd_id = a.maaltijd_id';
		if ($where !== null) {
			$sql.= ' WHERE '. $where;
		}
		$sql.= ' GROUP BY m.maaltijd_id';
		$sql.= ' ORDER BY datum ASC, tijd ASC';
		if (is_int($limit) && $limit > 0) {
			$sql.= ' LIMIT '. $limit;
		}
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, '\Taken\MLT\Maaltijd');
		return $result;
	}
	
	private static function updateMaaltijd(Maaltijd $maaltijd) {
		$sql = 'UPDATE mlt_maaltijden';
		$sql.= ' SET titel=?, aanmeld_limiet=?, datum=?, tijd=?, prijs=?, gesloten=?, laatst_gesloten=?, verwijderd=?, aanmeld_filter=?';
		$sql.= ' WHERE maaltijd_id=?';
		$values = array(
			$maaltijd->getTitel(),
			$maaltijd->getAanmeldLimiet(),
			$maaltijd->getDatum(),
			$maaltijd->getTijd(),
			$maaltijd->getPrijs(),
			$maaltijd->getIsGesloten(),
			$maaltijd->getLaatstGesloten(),
			$maaltijd->getIsVerwijderd(),
			$maaltijd->getAanmeldFilter(),
			$maaltijd->getMaaltijdId()
		);
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new \Exception('Update maaltijd faalt: $query->rowCount() ='. $query->rowCount());
		}
	}
	
	private static function newMaaltijd($mrid, $titel, $limiet, $datum, $tijd, $prijs, $filter) {
		$gesloten = true;
		$wanneer = date('Y-m-d H:i');
		if (strtotime($datum .' '. $tijd) > strtotime($wanneer)) {
			$gesloten = false;
			$wanneer = null;
		}
		$sql = 'INSERT INTO mlt_maaltijden';
		$sql.= ' (maaltijd_id, mlt_repetitie_id, titel, aanmeld_limiet, datum, tijd, prijs, gesloten, laatst_gesloten, verwijderd, aanmeld_filter)';
		$sql.= ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$values = array(null, $mrid, $titel, $limiet, $datum, $tijd, $prijs, $gesloten, $wanneer, false, $filter);
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		if ($query->rowCount() !== 1) {
			throw new \Exception('New maaltijd faalt: $query->rowCount() ='. $query->rowCount());
		}
		$maaltijd = new Maaltijd(intval($db->lastInsertId()), $mrid, $titel, $limiet, $datum, $tijd, $prijs, $gesloten, $wanneer, false, $filter);
		$aantal = 0;
		// aanmelden van leden met abonnement op deze repetitie
		if (!$gesloten && $mrid !== null) {
			$abonnementen = AbonnementenModel::getAbonnementenVoorRepetitie($mrid);
			foreach ($abonnementen as $abo) {
				if (AanmeldingenModel::checkAanmeldFilter($abo->getLidId(), $maaltijd->getAanmeldFilter())) {
					AanmeldingenModel::aanmeldenDoorAbonnement($maaltijd->getMaaltijdId(), $abo->getMaaltijdRepetitieId(), $abo->getLidId());
					$aantal++;
				}
			}
		}
		$maaltijd->setAantalAanmeldingen($aantal);
		return $maaltijd;
	}
	
	// Repetitie-Maaltijden ############################################################
	
	public static function getKomendeRepetitieMaaltijden($mrid) {
		return self::loadMaaltijden('mlt_repetitie_id = ? AND verwijderd = false AND datum >= ?', array($mrid, date('Y-m-d')));
	}
	
	public static function getKomendeOpenRepetitieMaaltijden($mrid) {
		return self::loadMaaltijden('mlt_repetitie_id = ? AND gesloten = false AND verwijderd = false AND datum >= ?', array($mrid, date('Y-m-d')));
	}
	
	public static function verwijderRepetitieMaaltijden($mrid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new \Exception('Verwijder repetitie-maaltijden faalt: Invalid $mrid ='. $mrid);
		}
		$sql = 'UPDATE mlt_maaltijden SET verwijderd = true WHERE mlt_repetitie_id = ?';
		$values = array($mrid);
		$db = \CsrPdo::instance();
		$query = $db->prepare($sql, $values);
		$query->execute($values);
		return $query->rowCount();
	}
	
	/**
	 * Called when a MaaltijdRepetitie is updated or is going to be deleted.
	 * 
	 * @param int $mrid
	 * @return boolean
	 */
	public static function existRepetitieMaaltijden($mrid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new \Exception('Exist repetitie-maaltijden faalt: Invalid $mrid ='. $mrid);
		}
		$sql = 'SELECT EXISTS (SELECT * FROM mlt_maaltijden WHERE mlt_repetitie_id = ?)';
		$values = array($mrid);
		$query = \CsrPdo::instance()->prepare($sql, $values);
		$query->execute($values);
		$result = (boolean) $query->fetchColumn();
		return $result;
	}
	
	public static function updateRepetitieMaaltijden(MaaltijdRepetitie $repetitie, $verplaats) {
		$db = \CsrPdo::instance();
		try {
			$db->beginTransaction();
			// update day of the week & check filter
			$updated = 0;
			$aanmeldingen = 0;
			$maaltijden = self::loadMaaltijden('verwijderd = false AND mlt_repetitie_id = ?', array($repetitie->getMaaltijdRepetitieId()));
			$filter = $repetitie->getAbonnementFilter();
			if (!empty($filter)) {
				$aanmeldingen = AanmeldingenModel::checkAanmeldingenFilter($filter, $maaltijden);
			}
			foreach ($maaltijden as $maaltijd) {
				if ($verplaats) {
					$datum = strtotime($maaltijd->getDatum());
					$shift = $repetitie->getDagVanDeWeek() - date('w', $datum);
					if ($shift > 0) {
						$datum = strtotime('+'. $shift .' days', $datum);
					}
					elseif ($shift < 0) {
						$datum = strtotime($shift .' days', $datum);
					}
					$maaltijd->setDatum(date('Y-m-d', $datum));
				}
				$maaltijd->setTitel($repetitie->getStandaardTitel());
				$maaltijd->setAanmeldLimiet($repetitie->getStandaardLimiet());
				$repetitie->setStandaardTijd($maaltijd->getTijd());
				$maaltijd->setPrijs($repetitie->getStandaardPrijs());
				$maaltijd->setAanmeldFilter($filter);
				try {
					self::updateMaaltijd($maaltijd);
					$updated++;
				}
				catch (\Exception $e) {
				}
			}
			$db->commit();
			return array($updated, $aanmeldingen);
		}
		catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}
	
	/**
	 * Maakt nieuwe maaltijden aan volgens de definitie van de maaltijd-repetitie.
	 * Alle leden met een abonnement hierop worden automatisch aangemeld.
	 * 
	 * @return Maaltijden[]
	 */
	public static function maakRepetitieMaaltijden(MaaltijdRepetitie $repetitie, $beginDatum, $eindDatum) {
		if ($repetitie->getPeriodeInDagen() < 1) {
			throw new \Exception('New repetitie-maaltijden faalt: $periode ='. $repetitie->getPeriodeInDagen());
		}
		$db = \CsrPdo::instance();
		try {
			$db->beginTransaction();
			// start at first occurence
			$shift = $repetitie->getDagVanDeWeek() - date('w', $beginDatum) + 7;
			$shift %= 7;
			if ($shift > 0) {
				$beginDatum = strtotime('+'. $shift .' days', $beginDatum);
			}
			$datum = $beginDatum;
			$corveerepetities = \Taken\CRV\CorveeRepetitiesModel::getRepetitiesVoorMaaltijdRepetitie($repetitie->getMaaltijdRepetitieId());
			$maaltijden = array();
			while ($datum <= $eindDatum) { // break after one
				$maaltijd = self::newMaaltijd(
					$repetitie->getMaaltijdRepetitieId(),
					$repetitie->getStandaardTitel(),
					$repetitie->getStandaardLimiet(),
					date('Y-m-d', $datum),
					$repetitie->getStandaardTijd(),
					$repetitie->getStandaardPrijs(),
					$repetitie->getAbonnementFilter()
				);
				foreach ($corveerepetities as $corveerepetitie) {
					\Taken\CRV\TakenModel::newRepetitieTaken($corveerepetitie, $datum, $datum, $maaltijd->getMaaltijdId()); // do not repeat within maaltijd period
				}
				$maaltijden[] = $maaltijd;
				if ($repetitie->getPeriodeInDagen() < 1) {
					break;
				}
				$datum = strtotime('+'. $repetitie->getPeriodeInDagen() .' days', $datum);
			}
			$db->commit();
			return $maaltijden;
		}
		catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}
}

?>