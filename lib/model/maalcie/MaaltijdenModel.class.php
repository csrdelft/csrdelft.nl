<?php

require_once 'model/entity/maalcie/Maaltijd.class.php';
require_once 'model/entity/maalcie/ArchiefMaaltijd.class.php';
require_once 'model/maalcie/CorveeRepetitiesModel.class.php';
require_once 'model/maalcie/MaaltijdAbonnementenModel.class.php';

/**
 * MaaltijdenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MaaltijdenModel extends PersistenceModel {

    const ORM = 'Maaltijd';
    const DIR = 'maalcie/';

    protected static $instance;

	/**
	 * Do NOT use @ and . in your primary keys or you WILL run into trouble here!
	 * 
	 * @param string $UUID
	 * @return PersistentEntity
	 */
	public static function getUUID($UUID) {
		$parts = explode('@', $UUID, 2);
		$primary_key_values = explode('.', $parts[0]);
		return static::instance()->retrieveByPrimaryKey($primary_key_values);
	}

	public static function openMaaltijd(Maaltijd $maaltijd) {
		if (!$maaltijd->gesloten) {
			throw new Exception('Maaltijd is al geopend');
		}
		$maaltijd->gesloten = false;
		static::instance()->update($maaltijd);
		return $maaltijd;
	}

	public static function sluitMaaltijd(Maaltijd $maaltijd) {
		if ($maaltijd->gesloten) {
			throw new Exception('Maaltijd is al gesloten');
		}
		$maaltijd->gesloten = true;
		$maaltijd->laatst_gesloten = date('Y-m-d H:i');
        static::instance()->update($maaltijd);
	}

	public static function getAlleMaaltijden() {
		return self::loadMaaltijden('verwijderd = false');
	}

	/**
	 * Haalt de maaltijden op voor het ingelode lid tussen de opgegeven data.
	 *
	 * @param int $van Timestamp
	 * @param int $tot Timestamp
	 * @return Maaltijd[] (implements Agendeerbaar)
	 * @throws Exception
	 */
	public static function getMaaltijdenVoorAgenda($van, $tot) {
		if (!is_int($van)) {
			throw new Exception('Invalid timestamp: $van getMaaltijdenVoorAgenda()');
		}
		if (!is_int($tot)) {
			throw new Exception('Invalid timestamp: $tot getMaaltijdenVoorAgenda()');
		}
		$maaltijden = self::loadMaaltijden('verwijderd = FALSE AND datum >= ? AND datum <= ?', array(date('Y-m-d', $van), date('Y-m-d', $tot)));
		$maaltijden = self::filterMaaltijdenVoorLid($maaltijden, LoginModel::getUid());
		return $maaltijden;
	}

	/**
	 * Haalt de maaltijden op die beschikbaar zijn voor aanmelding voor het lid in de ingestelde periode vooraf.
	 * 
	 * @param string $uid
	 * @return Maaltijd[]
	 */
	public static function getKomendeMaaltijdenVoorLid($uid) {
		$maaltijden = self::loadMaaltijden('verwijderd = FALSE AND datum >= ? AND datum <= ?', array(date('Y-m-d'), date('Y-m-d', strtotime(Instellingen::get('maaltijden', 'toon_ketzer_vooraf')))));
		$maaltijden = self::filterMaaltijdenVoorLid($maaltijden, $uid);
		return $maaltijden;
	}

	/**
	 * Haalt de maaltijden in het verleden op voor de ingestelde periode.
	 * 
	 * @return Maaltijd[]
	 */
	public static function getRecenteMaaltijden($timestamp, $limit = null) {
		$maaltijden = self::loadMaaltijden('verwijderd = FALSE AND datum >= ? AND datum <= ?', array(date('Y-m-d', $timestamp), date('Y-m-d')), $limit);
		$maaltijdenById = array();
		foreach ($maaltijden as $maaltijd) {
			$maaltijdenById[$maaltijd->maaltijd_id] = $maaltijd;
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
		$maaltijden = self::filterMaaltijdenVoorLid($maaltijden, LoginModel::getUid());
		if (!empty($maaltijden)) {
			return reset($maaltijden);
		}
		return false;
	}

	public static function getVerwijderdeMaaltijden() {
		return self::loadMaaltijden('verwijderd = true');
	}

	public static function getMaaltijd($mid, $verwijderd = false) {
		$maaltijd = self::loadMaaltijd($mid);
		if (!$verwijderd && $maaltijd->verwijderd) {
			throw new Exception('Maaltijd is verwijderd');
		}
		return $maaltijd;
	}

	private static function loadMaaltijd($mid) {
		if (!is_int($mid) || $mid <= 0) {
			throw new Exception('Load maaltijd faalt: Invalid $mid =' . $mid);
		}
		$maaltijden = self::loadMaaltijden('maaltijd_id = ?', array($mid), 1);
		if (!array_key_exists(0, $maaltijden)) {
			throw new Exception('Load maaltijd faalt: Not found $mid =' . $mid);
		}
		return $maaltijden[0];
	}

	public static function saveMaaltijd($mid, $mrid, $titel, $limiet, $datum, $tijd, $prijs, $filter, $omschrijving) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			$verwijderd = 0;
			if ($mid === null) {
				$maaltijd = self::newMaaltijd($mrid, $titel, $limiet, $datum, $tijd, $prijs, $filter, $omschrijving);
			} else {
				$maaltijd = self::getMaaltijd($mid);
				$maaltijd->titel = $titel;
				$maaltijd->aanmeld_limiet = $limiet;
				$maaltijd->datum = $datum;
				$maaltijd->tijd = $tijd;
				$maaltijd->prijs = $prijs;
				$maaltijd->aanmeld_filter = $filter;
				$maaltijd->omschrijving = $omschrijving;
                static::instance()->update($maaltijd);
				if (!$maaltijd->gesloten && $maaltijd->getBeginMoment() < time()) {
					MaaltijdenModel::sluitMaaltijd($maaltijd);
				}
				if (!$maaltijd->gesloten && !$maaltijd->verwijderd && !empty($filter)) {
					$verwijderd = MaaltijdAanmeldingenModel::checkAanmeldingenFilter($filter, array($maaltijd));
					$maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() - $verwijderd;
				}
			}
			$db->commit();
			return array($maaltijd, $verwijderd);
		} catch (\Exception $e) {
			$db->rollback();
			throw $e; // rethrow to controller
		}
	}

	public static function prullenbakLeegmaken() {
		$aantal = 0;
		$maaltijden = self::getVerwijderdeMaaltijden();
		foreach ($maaltijden as $maaltijd) {
			try {
				self::verwijderMaaltijd($maaltijd->maaltijd_id);
				$aantal++;
			} catch (\Exception $e) {
				setMelding($e->getMessage(), -1);
			}
		}
		return $aantal;
	}

	public static function verwijderMaaltijd($mid) {
		$maaltijd = self::loadMaaltijd($mid);
		\CorveeTakenModel::verwijderMaaltijdCorvee($mid); // delete corveetaken first (foreign key)
		if ($maaltijd->verwijderd) {
			if (\CorveeTakenModel::existMaaltijdCorvee($mid)) {
				throw new Exception('Er zitten nog bijbehorende corveetaken in de prullenbak. Verwijder die eerst definitief!');
			}
			self::deleteMaaltijd($mid); // definitief verwijderen
		} else {
			$maaltijd->verwijderd = true;
            static::instance()->update($maaltijd);
		}
	}

	private static function deleteMaaltijd($mid) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			MaaltijdAanmeldingenModel::deleteAanmeldingenVoorMaaltijd($mid); // delete aanmeldingen first (foreign key)
			$sql = 'DELETE FROM mlt_maaltijden';
			$sql.= ' WHERE maaltijd_id = ?';
			$values = array($mid);
			$query = $db->prepare($sql);
			$query->execute($values);
			if ($query->rowCount() !== 1) {
				throw new Exception('Delete maaltijd faalt: $query->rowCount() =' . $query->rowCount());
			}
			$db->commit();
		} catch (\Exception $e) {
			$db->rollBack();
			throw $e; // rethrow to controller
		}
	}

	public static function herstelMaaltijd($mid) {
		$maaltijd = self::loadMaaltijd($mid);
		if (!$maaltijd->verwijderd) {
			throw new Exception('Maaltijd is niet verwijderd');
		}
		$maaltijd->verwijderd = false;
        static::instance()->update($maaltijd);
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
			// Kan en mag aanmelden of mag maaltijdlijst zien en sluiten? Dan maaltijd ook zien.
			if (($maaltijd->aanmeld_limiet > 0 AND MaaltijdAanmeldingenModel::checkAanmeldFilter($uid, $maaltijd->aanmeld_filter)) OR $maaltijd->magBekijken($uid)) {
				$result[$maaltijd->maaltijd_id] = $maaltijd;
			}
		}
		return $result;
	}

	/**
	 * @param string $where
	 * @param array $values
	 * @param int $limit
	 * @return Maaltijd[]
	 */
	private static function loadMaaltijden($where = null, $values = array(), $limit = null) {
        return static::instance()->find($where, $values, null, 'datum ASC, tijd ASC', $limit)->fetchAll();
	}

	private static function newMaaltijd($mrid, $titel, $limiet, $datum, $tijd, $prijs, $filter, $omschrijving) {
		$gesloten = true;
		$wanneer = date('Y-m-d H:i');
		if (strtotime($datum . ' ' . $tijd) > strtotime($wanneer)) {
			$gesloten = false;
			$wanneer = null;
		}

		$maaltijd = new Maaltijd();
        $maaltijd->mlt_repetitie_id = $mrid;
        $maaltijd->titel = $titel;
        $maaltijd->datum = $datum;
        $maaltijd->tijd = $tijd;
        $maaltijd->prijs = $prijs;
        $maaltijd->gesloten = $gesloten;
        $maaltijd->laatst_gesloten = $wanneer;
        $maaltijd->gesloten = false;
        $maaltijd->aanmeld_filter = $filter;
        $maaltijd->aanmeld_limiet = $limiet;
        $maaltijd->omschrijving = $omschrijving;

        $maaltijd->maaltijd_id = static::instance()->create($maaltijd);

		$aantal = 0;
		// aanmelden van leden met abonnement op deze repetitie
		if (!$gesloten && $mrid !== null) {
			$abonnementen = MaaltijdAbonnementenModel::getAbonnementenVoorRepetitie($mrid);
			foreach ($abonnementen as $abo) {
				if (MaaltijdAanmeldingenModel::checkAanmeldFilter($abo->getUid(), $maaltijd->aanmeld_filter)) {
					MaaltijdAanmeldingenModel::aanmeldenDoorAbonnement($maaltijd->maaltijd_id, $abo->getMaaltijdRepetitieId(), $abo->getUid());
					$aantal++;
				}
			}
		}
		$maaltijd->aantal_aanmeldingen = $aantal;
		return $maaltijd;
	}

	// Archief-Maaltijden ############################################################

	/**
	 * @param Maaltijd[] $maaltijden
	 */
	public static function existArchiefMaaltijden(array $maaltijden) {
		$where = '(maaltijd_id=?';
		for ($i = sizeof($maaltijden); $i > 1; $i--) {
			$where.= ' OR maaltijd_id=?';
		}
		$where.= ')';
		$maaltijdenById = array();
		foreach ($maaltijden as $maaltijd) {
			$maaltijdenById[$maaltijd->maaltijd_id] = $maaltijd;
		}
		$archief = self::loadArchiefMaaltijden($where, array_keys($maaltijdenById));
		foreach ($archief as $maaltijd) {
			$maaltijdenById[$maaltijd->maaltijd_id]->setArchief($maaltijd);
		}
	}

	/**
	 * Haalt de archiefmaaltijden op tussen de opgegeven data.
	 *
	 * @param int $van Timestamp
	 * @param int $tot Timestamp
	 * @return ArchiefMaaltijd[] (implements Agendeerbaar)
	 * @throws Exception
	 */
	public static function getArchiefMaaltijdenTussen($van = null, $tot = null) {
		if ($van === null) { // RSS
			$van = 0;
		} elseif (!is_int($van)) {
			throw new Exception('Invalid timestamp: $van getArchiefMaaltijden()');
		}
		if ($tot === null) { // RSS
			$tot = time();
		} elseif (!is_int($tot)) {
			throw new Exception('Invalid timestamp: $tot getArchiefMaaltijden()');
		}
		return self::loadArchiefMaaltijden('datum >= ? AND datum <= ?', array(date('Y-m-d', $van), date('Y-m-d', $tot)));
	}

	/**
	 * @param null $where
	 * @param array $values
	 * @param null $limit
	 * @return Maaltijd[]
	 */
	private static function loadArchiefMaaltijden($where = null, $values = array(), $limit = null) {
		$sql = 'SELECT maaltijd_id, titel, datum, tijd, prijs, aanmeldingen';
		$sql.= ' FROM mlt_archief';
		if ($where !== null) {
			$sql.= ' WHERE ' . $where;
		}
		$sql.= ' ORDER BY datum DESC, tijd DESC';
		if (is_int($limit) && $limit > 0) {
			$sql.= ' LIMIT ' . $limit;
		}
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		$result = $query->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, 'ArchiefMaaltijd');
		return $result;
	}

	public static function archiveerOudeMaaltijden($van, $tot) {
		if (!is_int($van) || !is_int($tot)) {
			throw new Exception('Invalid timestamp: archiveerOudeMaaltijden()');
		}
		$errors = array();
		$maaltijden = self::loadMaaltijden('verwijderd = FALSE AND datum >= ? AND datum <= ?', array(date('Y-m-d', $van), date('Y-m-d', $tot)));
		foreach ($maaltijden as $maaltijd) {
			try {
				self::verplaatsNaarArchief($maaltijd);
				if (\CorveeTakenModel::existMaaltijdCorvee($maaltijd->maaltijd_id)) {
					setMelding($maaltijd->datum . ' ' . $maaltijd->titel . ' heeft nog gekoppelde corveetaken!', 2);
				}
			} catch (\Exception $e) {
				$errors[] = $e;
				setMelding($e->getMessage(), -1);
			}
		}
		return array($errors, sizeof($maaltijden));
	}

	private static function verplaatsNaarArchief(Maaltijd $maaltijd) {
		$archief = new ArchiefMaaltijd(
				$maaltijd->maaltijd_id, $maaltijd->titel, $maaltijd->datum, $maaltijd->tijd, $maaltijd->prijs, MaaltijdAanmeldingenModel::getAanmeldingenVoorMaaltijd($maaltijd)
		);
		self::verwijderMaaltijd($maaltijd->maaltijd_id);
		self::newArchiefMaaltijd($archief); // alleen als de maaltijd definitief verwijderd is
		return $archief;
	}

	private static function newArchiefMaaltijd(ArchiefMaaltijd $archief) {
		$db = \Database::instance();
		try {
			$db->beginTransaction();
			$sql = 'INSERT INTO mlt_archief';
			$sql.= ' (maaltijd_id, titel, datum, tijd, prijs, aanmeldingen)';
			$sql.= ' VALUES (?, ?, ?, ?, ?, ?)';
			$values = array(
				$archief->getMaaltijdId(),
				$archief->getTitel(),
				$archief->getDatum(),
				$archief->getTijd(),
				$archief->getPrijs(),
				$archief->getAanmeldingen()
			);
			$query = $db->prepare($sql);
			$query->execute($values);
			if ($query->rowCount() !== 1) {
				$db->rollBack();
				throw new Exception('New archief-maaltijd faalt: $query->rowCount() =' . $query->rowCount());
			}
			$db->commit();
		} catch (\Exception $e) {
			$db->rollBack();
			throw $e; // rethrow to controller
		}
	}

	// Repetitie-Maaltijden ############################################################

	public static function getKomendeRepetitieMaaltijden($mrid) {
		return self::loadMaaltijden('mlt_repetitie_id = ? AND verwijderd = FALSE AND datum >= ?', array($mrid, date('Y-m-d')));
	}

	public static function getKomendeOpenRepetitieMaaltijden($mrid) {
		return self::loadMaaltijden('mlt_repetitie_id = ? AND gesloten = FALSE AND verwijderd = FALSE AND datum >= ?', array($mrid, date('Y-m-d')));
	}

	public static function verwijderRepetitieMaaltijden($mrid) {
        $maaltijden = static::instance()->find('mlt_repetitie_id = ?', array($mrid));
        foreach ($maaltijden as $maaltijd) {
            $maaltijd->verwijderd = true;
            static::instance()->update($maaltijd);
        }
	}

	/**
	 * Called when a MaaltijdRepetitie is updated or is going to be deleted.
	 *
	 * @param int $mrid
	 * @return bool
	 * @throws Exception
	 */
	public function existRepetitieMaaltijden($mrid) {
        return $this->count('mlt_repetitie_id = ?', array($mrid)) > 0;
	}

	public function updateRepetitieMaaltijden(MaaltijdRepetitie $repetitie, $verplaats) {
        // update day of the week & check filter
        $updated = 0;
        $aanmeldingen = 0;
        $maaltijden = self::loadMaaltijden('verwijderd = FALSE AND mlt_repetitie_id = ?', array($repetitie->mlt_repetitie_id));
        $filter = $repetitie->abonnement_filter;
        if (!empty($filter)) {
            $aanmeldingen = MaaltijdAanmeldingenModel::checkAanmeldingenFilter($filter, $maaltijden);
        }
        foreach ($maaltijden as $maaltijd) {
            if ($verplaats) {
                $datum = strtotime($maaltijd->datum);
                $shift = $repetitie->dag_vd_week - date('w', $datum);
                if ($shift > 0) {
                    $datum = strtotime('+' . $shift . ' days', $datum);
                } elseif ($shift < 0) {
                    $datum = strtotime($shift . ' days', $datum);
                }
                $maaltijd->datum = date('Y-m-d', $datum);
            }
            $maaltijd->titel = $repetitie->standaard_titel;
            $maaltijd->aanmeld_limiet = $repetitie->standaard_limiet;
            $repetitie->standaard_tijd = $maaltijd->tijd;
            $maaltijd->prijs = $repetitie->standaard_prijs;
            $maaltijd->aanmeld_filter = $filter;
            try {
                $this->update($maaltijd);
                $updated++;
            } catch (\Exception $e) {

            }
        }
        return array($updated, $aanmeldingen);
	}

	/**
	 * Maakt nieuwe maaltijden aan volgens de definitie van de maaltijd-repetitie.
	 * Alle leden met een abonnement hierop worden automatisch aangemeld.
     *
     * Moet in een transactie gedraaid worden.
	 *
	 * @param MaaltijdRepetitie $repetitie
	 * @param $beginDatum
	 * @param $eindDatum
	 * @return Maaltijd[]
	 * @throws Exception
	 */
	public function maakRepetitieMaaltijden(MaaltijdRepetitie $repetitie, $beginDatum, $eindDatum) {
		if ($repetitie->periode_in_dagen < 1) {
			throw new Exception('New repetitie-maaltijden faalt: $periode =' . $repetitie->periode_in_dagen);
		}

        // start at first occurence
        $shift = $repetitie->dag_vd_week - date('w', $beginDatum) + 7;
        $shift %= 7;
        if ($shift > 0) {
            $beginDatum = strtotime('+' . $shift . ' days', $beginDatum);
        }
        $datum = $beginDatum;
        $corveerepetities = \CorveeRepetitiesModel::getRepetitiesVoorMaaltijdRepetitie($repetitie->mlt_repetitie_id);
        $maaltijden = array();
        while ($datum <= $eindDatum) { // break after one
            $maaltijd = static::newMaaltijd(
                $repetitie->mlt_repetitie_id,
                $repetitie->standaard_titel,
                $repetitie->standaard_limiet,
                date('Y-m-d', $datum),
                $repetitie->standaard_tijd,
                $repetitie->standaard_prijs,
                $repetitie->abonnement_filter,
                null);

            foreach ($corveerepetities as $corveerepetitie) {
                \CorveeTakenModel::newRepetitieTaken($corveerepetitie, $datum, $datum, intval($maaltijd->maaltijd_id)); // do not repeat within maaltijd period
            }
            $maaltijden[] = $maaltijd;
            if ($repetitie->periode_in_dagen < 1) {
                break;
            }
            $datum = strtotime('+' . $repetitie->periode_in_dagen . ' days', $datum);
        }
        return $maaltijden;
    }

}

?>