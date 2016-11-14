<?php

require_once 'model/entity/maalcie/MaaltijdAbonnement.class.php';
require_once 'model/maalcie/MaaltijdAanmeldingenModel.class.php';
require_once 'model/maalcie/MaaltijdRepetitiesModel.class.php';

/**
 * MaaltijdAbonnementenModel.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class MaaltijdAbonnementenModel extends PersistenceModel {

    const ORM = 'MaaltijdAbonnement';
    const DIR = 'maalcie/';

    protected static $instance;

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
	}

	public function getHeeftAbonnement($mrid, $uid) {
        $abonnement = new MaaltijdAbonnement();
        $abonnement->mlt_repetitie_id = $mrid;
        $abonnement->uid = $uid;
        return $this->exists($abonnement);
	}

	/**
	 * Bouwt matrix voor alle repetities en abonnementen van alle leden
	 *
	 * @param bool $alleenNovieten
	 * @param bool $alleenWaarschuwingen
	 * @param null $ingeschakeld
	 * @param null $voorLid
	 * @return MaaltijdAbonnement [uid][mrid]
	 * @throws Exception
	 */
	public static function getAbonnementenMatrix($alleenNovieten = false, $alleenWaarschuwingen = false, $ingeschakeld = null, $voorLid = null) {
		$repById = MaaltijdRepetitiesModel::instance()->getAlleRepetities(true); // grouped by mrid
		$abos = self::loadLedenAbonnementen($alleenNovieten, $alleenWaarschuwingen, $ingeschakeld, $voorLid);
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
			} elseif (!MaaltijdAanmeldingenModel::checkAanmeldFilter($uid, $abo['filter'])) {
				$abonnement->foutmelding = 'Niet toegestaan vanwege aanmeldrestrictie: ' . $abo['filter'];
			} elseif ($alleenWaarschuwingen) {
				continue;
			}
			$matrix[$uid][$mrid] = $abonnement;
		}
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

	private static function loadLedenAbonnementen($alleenNovieten = false, $alleenWaarschuwingen = false, $ingeschakeld = null, $voorLid = null) {
		$sql = 'SELECT lid.uid AS van, r.mlt_repetitie_id AS mrid,';
		$sql.= ' r.abonnement_filter AS filter,'; // controleer later
		$sql.= ' (r.abonneerbaar = false) AS abo_err, (lid.status NOT IN("S_LID", "S_GASTLID", "S_NOVIET")) AS status_err,';
		$sql.= ' (EXISTS ( SELECT * FROM mlt_abonnementen AS a WHERE a.mlt_repetitie_id = mrid AND a.uid = van )) AS abo';
		$sql.= ' FROM profielen AS lid, mlt_repetities AS r';
		$values = array();
		if ($alleenWaarschuwingen) {
			$sql.= ' HAVING abo AND (filter != "" OR abo_err OR status_err)'; // niet-leden met abo
		} elseif ($voorLid !== null) { // alles voor specifiek lid
			$sql.= ' WHERE lid.uid = ?';
			$values[] = $voorLid;
		} elseif ($alleenNovieten) { // alles voor novieten
			$sql.= ' WHERE lid.status = "S_NOVIET"';
		} elseif ($ingeschakeld === true) {
			$sql.= ' HAVING abo = ?';
			$values[] = $ingeschakeld;
		} else { // abonneerbaar alleen voor leden
			$sql.= ' WHERE lid.status IN("S_LID", "S_GASTLID", "S_NOVIET")';
		}
		$sql.= ' ORDER BY lid.achternaam, lid.voornaam ASC';
		$db = \Database::instance();
		$query = $db->prepare($sql);
		$query->execute($values);
		$result = $query->fetchAll();
		return $result;
	}

	public function getAbonnementenVoorRepetitie($mrid) {
        return $this->find('mlt_repetitie_id = ?', array($mrid));
	}

	public function getAbonnementenVanNovieten() {
		$matrix_repetities = static::getAbonnementenMatrix(true);
		return $matrix_repetities[0];
	}

    /**
     * @param $abo MaaltijdAbonnement
     * @return false|int
     * @throws Exception
     */
	public function inschakelenAbonnement($abo) {
		$repetitie = MaaltijdRepetitiesModel::instance()->getRepetitie($abo->mlt_repetitie_id);
		if (!$repetitie->abonneerbaar) {
			throw new Exception('Niet abonneerbaar');
		}
		if ($this->exists($abo)) {
			throw new Exception('Abonnement al ingeschakeld');
		}
		if (!MaaltijdAanmeldingenModel::checkAanmeldFilter($abo->uid, $repetitie->abonnement_filter)) {
			throw new Exception('Niet toegestaan vanwege aanmeldrestrictie: ' . $repetitie->abonnement_filter);
		}

        $abo->van_uid = $abo->uid;
        $abo->wanneer_ingeschakeld = date('Y-m-d H:i');
        static::instance()->create($abo);

        $aantal = MaaltijdAanmeldingenModel::aanmeldenVoorKomendeRepetitieMaaltijden($abo->mlt_repetitie_id, $abo->uid);
		return $aantal;
	}

	public function inschakelenAbonnementVoorNovieten($mrid) {
		$novieten = ProfielModel::instance()->find('status = "S_NOVIET"');

        $aantal = 0;
        foreach ($novieten as $noviet) {
            $repetitie = MaaltijdRepetitiesModel::instance()->retrieveByPrimaryKey(array($mrid));
            if (!MaaltijdAanmeldingenModel::checkAanmeldFilter($noviet->uid, $repetitie->abonnement_filter)) {
                continue;
            }

            $abo = new MaaltijdAbonnement();
            $abo->mlt_repetitie_id = $mrid;
            $abo->uid = $noviet->uid;
            $abo->wanneer_ingeschakeld = date('Y-m-d H:i');

            $this->create($abo);
            MaaltijdAanmeldingenModel::aanmeldenVoorKomendeRepetitieMaaltijden($mrid, $uid);
            $aantal += 1;
        }

        return $aantal;
	}

	public function uitschakelenAbonnement($mrid, $uid) {
		if (!$this->getHeeftAbonnement($mrid, $uid)) {
			throw new Exception('Abonnement al uitgeschakeld');
		}
		$aantal = $this->deleteByPrimaryKey(array($mrid, $uid));
		$abo = new MaaltijdAbonnement();
        $abo->mlt_repetitie_id = $mrid;
		$abo->van_uid = $uid;
		return array($abo, $aantal);
	}

	/**
	 * Called when a MaaltijdRepetitie is being deleted.
	 * This is only possible after all MaaltijdAanmeldingen are deleted of this MaaltijdAbonnement,
	 * by deleting the Maaltijden (db foreign key door_abonnement)
	 *
	 * @param $mrid
	 * @return int amount of deleted abos
	 * @throws Exception
	 */
	public function verwijderAbonnementen($mrid) {
		$abos = $this->find('mlt_repetitie_id = ?', array($mrid));
        $aantal = count($abos);
        foreach ($abos as $abo) {
            $this->delete($abo);
        }
		return $aantal;
	}

	/**
	 * Called when a Lid is being made Lid-af.
	 * All linked MaaltijdAanmeldingen are deleted of this MaaltijdAbonnement.
	 * 
	 * @return int amount of deleted abos
	 */
	public function verwijderAbonnementenVoorLid($uid) {
		$abos = $this->getAbonnementenVoorLid($uid);
		$aantal = 0;
		foreach ($abos as $abo) {
			$aantal += $this->delete($abo);
		}
		if (sizeof($abos) !== $aantal) {
			setMelding('Niet alle abonnementen zijn uitgeschakeld!', -1);
		}
		return $aantal;
	}
}

?>