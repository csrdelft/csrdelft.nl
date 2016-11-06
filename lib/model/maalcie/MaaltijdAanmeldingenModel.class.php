<?php

require_once 'model/entity/maalcie/MaaltijdAanmelding.class.php';
require_once 'model/maalcie/MaaltijdenModel.class.php';

/**
 * MaaltijdAanmeldingenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MaaltijdAanmeldingenModel extends PersistenceModel  {

    const ORM = 'MaaltijdAanmelding';
    const DIR = 'maalcie/';

    protected static $instance;

	public static function aanmeldenVoorMaaltijd($mid, $uid, $doorUid, $aantalGasten = 0, $beheer = false, $gastenEetwens = '') {
		$maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid);
		if (!$maaltijd->gesloten && $maaltijd->getBeginMoment() < strtotime(date('Y-m-d H:i'))) {
			MaaltijdenModel::instance()->sluitMaaltijd($maaltijd);
		}
		if (!$beheer) {
			if (!self::checkAanmeldFilter($uid, $maaltijd->aanmeld_filter)) {
				throw new Exception('Niet toegestaan vanwege aanmeldrestrictie: ' . $maaltijd->aanmeld_filter);
			}
			if ($maaltijd->gesloten) {
				throw new Exception('Maaltijd is gesloten');
			}
			if ($maaltijd->getAantalAanmeldingen() >= $maaltijd->aanmeld_limiet) {
				throw new Exception('Maaltijd zit al vol');
			}
		}
		if (self::getIsAangemeld($mid, $uid)) {
			if (!$beheer) {
				throw new Exception('Al aangemeld');
			}
			// aanmelding van lid updaten met aantal gasen door beheerder
			$aanmelding = self::loadAanmelding($mid, $uid);
			$verschil = $aantalGasten - $aanmelding->aantal_gasten;
			if ($verschil === 0) {
				throw new Exception('Al aangemeld met ' . $aantalGasten . ' gasten');
			}
			$aanmelding->aantal_gasten = $aantalGasten;
			$aanmelding->laatst_gewijzigd = date('Y-m-d H:i');
			self::updateAanmelding($aanmelding);
			$maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() + $verschil;
		} else {
			$aanmelding = self::newAanmelding($mid, $uid, $aantalGasten, $gastenEetwens, null, $doorUid);
			$maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() + 1 + $aantalGasten;
		}
		$aanmelding->maaltijd = $maaltijd;
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
		$maaltijden = MaaltijdenModel::instance()->getKomendeOpenRepetitieMaaltijden($mrid);
		if (empty($maaltijden)) {
			return;
		}
		$byMid = array();
		foreach ($maaltijden as $maaltijd) {
			if (!$maaltijd->gesloten && !$maaltijd->verwijderd) {
				$byMid[$maaltijd->maaltijd_id] = $maaltijd;
			}
		}
		$aanmeldingen = self::getAanmeldingenVoorLid($byMid, $uid);
		$aantal = 0;
		foreach ($aanmeldingen as $mid => $aanmelding) {
			if ($mrid === $aanmelding->door_abonnement) {
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
		$maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid);
		if (!$maaltijd->gesloten && $maaltijd->getBeginMoment() < time()) {
			MaaltijdenModel::instance()->sluitMaaltijd($maaltijd);
		}
		if (!$beheer && $maaltijd->gesloten) {
			throw new Exception('Maaltijd is gesloten');
		}
		$aanmelding = self::loadAanmelding($mid, $uid);
		self::deleteAanmeldingen($mid, $uid);
		$maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() - 1 - $aanmelding->aantal_gasten;
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
			$maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid);
			if ($maaltijd->gesloten) {
				throw new Exception('Maaltijd is gesloten');
			}
			$aanmelding = self::loadAanmelding($mid, $uid);
			$verschil = $gasten - $aanmelding->aantal_gasten;
			if ($maaltijd->getAantalAanmeldingen() + $verschil > $maaltijd->aanmeld_limiet) {
				throw new Exception('Maaltijd zit te vol');
			}
			if ($aanmelding->aantal_gasten !== $gasten) {
				$aanmelding->laatst_gewijzigd = date('Y-m-d H:i');
			}
			$aanmelding->aantal_gasten = $gasten;
			self::updateAanmelding($aanmelding);
			$maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() + $verschil;
			$aanmelding->maaltijd = $maaltijd;
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
			$maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid);
			if ($maaltijd->gesloten) {
				throw new Exception('Maaltijd is gesloten');
			}
			$aanmelding = self::loadAanmelding($mid, $uid);
			if ($aanmelding->aantal_gasten <= 0) {
				throw new Exception('Geen gasten aangemeld');
			}
			$aanmelding->maaltijd = $maaltijd;
			$aanmelding->gasten_eetwens = $opmerking;
			self::updateAanmelding($aanmelding);
			$db->commit();
			return $aanmelding;
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

	public static function getAanmeldingenVoorMaaltijd(Maaltijd $maaltijd) {
		$aanmeldingen = self::loadAanmeldingen(array($maaltijd->maaltijd_id));
		$lijst = array();
		foreach ($aanmeldingen as $aanmelding) {
			$aanmelding->maaltijd = $maaltijd;
			$naam = ProfielModel::getNaam($aanmelding->uid, 'streeplijst');
			$lijst[$naam] = $aanmelding;
			for ($i = $aanmelding->aantal_gasten; $i > 0; $i--) {
				$gast = new MaaltijdAanmelding();
				$gast->door_uid = ($aanmelding->uid);
				$lijst[$naam . 'gast' . $i] = $gast;
			}
		}
		ksort($lijst);
		return $lijst;
	}

	public static function getRecenteAanmeldingenVoorLid($uid, $timestamp) {
		$maaltijdenById = MaaltijdenModel::instance()->getRecenteMaaltijden($timestamp);
		return MaaltijdAanmeldingenModel::getAanmeldingenVoorLid($maaltijdenById, $uid);
	}

	public static function getAanmeldingenVoorLid($maaltijdenById, $uid) {
		if (empty($maaltijdenById)) {
			return $maaltijdenById; // array()
		}
		$aanmeldingen = self::loadAanmeldingen(array_keys($maaltijdenById), $uid);
		$result = array();
		foreach ($aanmeldingen as $aanmelding) {
			$aanmelding->maaltijd = $maaltijdenById[$aanmelding->maaltijd_id];
			$result[$aanmelding->maaltijd_id] = $aanmelding;
		}
		return $result;
	}

	public static function getIsAangemeld($mid, $uid, $doorAbo = null) {
        $aanmelding = new MaaltijdAanmelding();
        $aanmelding->maaltijd_id = $mid;
        $aanmelding->uid = $uid;
        if ($doorAbo) {
            $aanmelding->door_abonnement = $doorAbo;
        }
        return static::instance()->exists($aanmelding);
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
        $aanmelding = new MaaltijdAanmelding();
        $aanmelding->uid = $uid;
        $aanmelding->gasten = $gasten;
        $aanmelding->gasten_eetwens = $opmerking;
        $aanmelding->door_abonnement = $doorAbo;
        $aanmelding->door_uid = $doorUid;
        $aanmelding->laatst_gewijzigd = date('Y-m-d H:i');
        if ($mid == null) {  // Alle komende maaltijden
            $maaltijden = MaaltijdenModel::instance()->find("mlt_repetitie_id = ? AND gesloten = false AND verwijderd = false AND datum >= ?", array($doorAbo, date('Y-m-d')));
            foreach ($maaltijden as $maaltijd) {
                $aanmelding->maaltijd_id = $maaltijd->maaltijd_id;
                if (!static::instance()->exists($aanmelding)) {
                    static::instance()->create($aanmelding);
                }
            }
            return $maaltijden->rowCount();
        } else {
            $aanmelding->maaltijd_id = $mid;
            static::instance()->create($aanmelding);
            return $aanmelding;
        }
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
			$aanmelding->aantal_gasten,
			$aanmelding->gasten_eetwens,
			$aanmelding->door_abonnement,
			$aanmelding->door_uid,
			$aanmelding->laatst_gewijzigd,
			$aanmelding->maaltijd_id,
			$aanmelding->uid
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
			if (!$maaltijd->gesloten && !$maaltijd->verwijderd) {
				$mids[] = $maaltijd->maaltijd_id;
			}
		}
		if (empty($mids)) {
			return 0;
		}
		$aantal = 0;
		$aanmeldingen = self::loadAanmeldingen($mids);
		foreach ($aanmeldingen as $aanmelding) { // check filter voor elk aangemeld lid
			$uid = $aanmelding->uid;
			if (!self::checkAanmeldFilter($uid, $filter)) { // verwijder aanmelding indien niet toegestaan
				$aantal += self::deleteAanmeldingen($aanmelding->maaltijd_id, $uid);
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
		if (!self::checkAanmeldFilter($uid, $repetitie->abonnement_filter)) {
			throw new Exception('Niet toegestaan vanwege aanmeldrestrictie: ' . $repetitie->abonnement_filter);
		}
		return self::newAanmelding(null, $uid, 0, '', $mrid, null);
	}

}
