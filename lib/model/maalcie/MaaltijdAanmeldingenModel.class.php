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

	public function aanmeldenVoorMaaltijd($mid, $uid, $doorUid, $aantalGasten = 0, $beheer = false, $gastenEetwens = '') {
		$maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid);
		if (!$maaltijd->gesloten && $maaltijd->getBeginMoment() < strtotime(date('Y-m-d H:i'))) {
			MaaltijdenModel::instance()->sluitMaaltijd($maaltijd);
		}
		if (!$beheer) {
			if (!$this->checkAanmeldFilter($uid, $maaltijd->aanmeld_filter)) {
				throw new Exception('Niet toegestaan vanwege aanmeldrestrictie: ' . $maaltijd->aanmeld_filter);
			}
			if ($maaltijd->gesloten) {
				throw new Exception('Maaltijd is gesloten');
			}
			if ($maaltijd->getAantalAanmeldingen() >= $maaltijd->aanmeld_limiet) {
				throw new Exception('Maaltijd zit al vol');
			}
		}

		if ($this->getIsAangemeld($mid, $uid)) {
			if (!$beheer) {
				throw new Exception('Al aangemeld');
			}
			// aanmelding van lid updaten met aantal gasten door beheerder
			$aanmelding = $this->loadAanmelding($mid, $uid);
			$verschil = $aantalGasten - $aanmelding->aantal_gasten;
			if ($verschil === 0) {
				throw new Exception('Al aangemeld met ' . $aantalGasten . ' gasten');
			}
			$aanmelding->aantal_gasten = $aantalGasten;
			$aanmelding->laatst_gewijzigd = date('Y-m-d H:i');
            $this->update($aanmelding);
			$maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() + $verschil;
		} else {
            $aanmelding = new MaaltijdAanmelding();
            $aanmelding->maaltijd_id = $mid;
            $aanmelding->uid = $uid;
            $aanmelding->door_uid = $doorUid;
            $aanmelding->aantal_gasten = $aantalGasten;
            $aanmelding->gasten_eetwens = $gastenEetwens;
            $aanmelding->laatst_gewijzigd = date('Y-m-d H:i');

            $this->create($aanmelding);

			$maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() + 1 + $aantalGasten;
		}
		$aanmelding->maaltijd = $maaltijd;
		return $aanmelding;
	}

	public function aanmeldenDoorAbonnement($mid, $mrid, $uid) {
        $aanmelding = new MaaltijdAanmelding();
        $aanmelding->maaltijd_id = $mid;
        $aanmelding->uid = $uid;
        $aanmelding->door_uid = $uid;
        $aanmelding->door_abonnement = $mrid;
        $aanmelding->laatst_gewijzigd = date('Y-m-d H:i');
        $aanmelding->gasten_eetwens = '';

        if (!$this->exists($aanmelding)) {
            $this->create($aanmelding);
        }
    }

    /**
     * Called when a MaaltijdAbonnement is being deleted (turned off) or a MaaltijdRepetitie is being deleted.
     *
     * @param int $mrid id van de betreffede MaaltijdRepetitie
     * @param type $uid Lid voor wie het MaaltijdAbonnement wordt uitschakeld
     * @return int|void
     */
	public function afmeldenDoorAbonnement($mrid, $uid) {
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
		$aanmeldingen = $this->getAanmeldingenVoorLid($byMid, $uid);
		$aantal = 0;
		foreach ($aanmeldingen as $mid => $aanmelding) {
			if ($mrid === $aanmelding->door_abonnement) {
                $this->deleteByPrimaryKey(array($mid, $uid));
				$aantal++;
			}
		}
		return $aantal;
	}

	public function afmeldenDoorLid($mid, $uid, $beheer = false) {
		if (!$this->getIsAangemeld($mid, $uid)) {
			throw new Exception('Niet aangemeld');
		}
		$maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid);
		if (!$maaltijd->gesloten && $maaltijd->getBeginMoment() < time()) {
			MaaltijdenModel::instance()->sluitMaaltijd($maaltijd);
		}
		if (!$beheer && $maaltijd->gesloten) {
			throw new Exception('Maaltijd is gesloten');
		}
		$aanmelding = $this->loadAanmelding($mid, $uid);
		$this->deleteByPrimaryKey(array($mid, $uid));
		$maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() - 1 - $aanmelding->aantal_gasten;
		return $maaltijd;
	}

	public function saveGasten($mid, $uid, $gasten) {
		if (!is_int($mid) || $mid <= 0) {
			throw new Exception('Save gasten faalt: Invalid $mid =' . $mid);
		}
		if (!is_int($gasten) || $gasten < 0) {
			throw new Exception('Save gasten faalt: Invalid $gasten =' . $gasten);
		}
		if (!$this->getIsAangemeld($mid, $uid)) {
			throw new Exception('Niet aangemeld');
		}

        $maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid);
        if ($maaltijd->gesloten) {
            throw new Exception('Maaltijd is gesloten');
        }
        $aanmelding = $this->loadAanmelding($mid, $uid);
        $verschil = $gasten - $aanmelding->aantal_gasten;
        if ($maaltijd->getAantalAanmeldingen() + $verschil > $maaltijd->aanmeld_limiet) {
            throw new Exception('Maaltijd zit te vol');
        }
        if ($aanmelding->aantal_gasten !== $gasten) {
            $aanmelding->laatst_gewijzigd = date('Y-m-d H:i');
        }
        $aanmelding->aantal_gasten = $gasten;
        $this->update($aanmelding);
        $maaltijd->aantal_aanmeldingen = $maaltijd->getAantalAanmeldingen() + $verschil;
        $aanmelding->maaltijd = $maaltijd;
        return $aanmelding;
	}

	public function saveGastenEetwens($mid, $uid, $opmerking) {
		if (!is_int($mid) || $mid <= 0) {
			throw new Exception('Save gasten eetwens faalt: Invalid $mid =' . $mid);
		}
		if (!$this->getIsAangemeld($mid, $uid)) {
			throw new Exception('Niet aangemeld');
		}

        $maaltijd = MaaltijdenModel::instance()->getMaaltijd($mid);
        if ($maaltijd->gesloten) {
            throw new Exception('Maaltijd is gesloten');
        }
        $aanmelding = $this->loadAanmelding($mid, $uid);
        if ($aanmelding->aantal_gasten <= 0) {
            throw new Exception('Geen gasten aangemeld');
        }
        $aanmelding->maaltijd = $maaltijd;
        $aanmelding->gasten_eetwens = $opmerking;
        $this->update($aanmelding);
        return $aanmelding;
	}

	public function getAanmeldingenVoorMaaltijd(Maaltijd $maaltijd) {
        $aanmeldingen = $this->find('maaltijd_id = ?', array($maaltijd->maaltijd_id));
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

	public function getRecenteAanmeldingenVoorLid($uid, $timestamp) {
		$maaltijdenById = MaaltijdenModel::instance()->getRecenteMaaltijden($timestamp);
		return $this->getAanmeldingenVoorLid($maaltijdenById, $uid);
	}

	public function getAanmeldingenVoorLid($maaltijdenById, $uid) {
		if (empty($maaltijdenById)) {
			return $maaltijdenById; // array()
		}

		$aanmeldingen = array();
		foreach ($maaltijdenById as $maaltijd) {
            $aanmeldingen = array_merge($aanmeldingen, $this->find('maaltijd_id = ? AND uid = ?', array($maaltijd->maaltijd_id, $uid))->fetchAll());
        }

		$result = array();
		foreach ($aanmeldingen as $aanmelding) {
			$aanmelding->maaltijd = $maaltijdenById[$aanmelding->maaltijd_id];
			$result[$aanmelding->maaltijd_id] = $aanmelding;
		}
		return $result;
	}

	public function getIsAangemeld($mid, $uid) {
        $aanmelding = new MaaltijdAanmelding();
        $aanmelding->maaltijd_id = $mid;
        $aanmelding->uid = $uid;

        return $this->exists($aanmelding);
	}

	public function loadAanmelding($mid, $uid) {
        $aanmelding = $this->retrieveByPrimaryKey(array($mid, $uid));
		if ($aanmelding === false) {
			throw new Exception('Load aanmelding faalt: Not found $mid =' . $mid);
		}
		return $aanmelding;
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
	public function deleteAanmeldingenVoorMaaltijd($mid) {
        $aanmeldingen = $this->find('maaltijd_id = ?', array($mid));
        foreach ($aanmeldingen as $aanmelding) {
            $this->delete($aanmelding);
        }
	}

	/**
	 * Controleer of alle aanmeldingen voor de maaltijden nog in overeenstemming zijn met het aanmeldfilter.
	 *
	 * @param Maaltijd[] $maaltijden
	 * @return int|void
	 */
	public function checkAanmeldingenFilter($filter, $maaltijden) {
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
        $aanmeldingen = array();
        foreach ($mids as $mid) {
            array_merge($aanmeldingen, $this->find('maaltijd_id = ?', array($mid))->fetchAll());
        }
		foreach ($aanmeldingen as $aanmelding) { // check filter voor elk aangemeld lid
			$uid = $aanmelding->uid;
			if (!$this->checkAanmeldFilter($uid, $filter)) { // verwijder aanmelding indien niet toegestaan
                $aantal += 1 + $aanmelding->aantal_gasten;
                $this->delete($aanmelding);
			}
		}
		return $aantal;
	}

	public function checkAanmeldFilter($uid, $filter) {
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
	public function aanmeldenVoorKomendeRepetitieMaaltijden($mrid, $uid) {
		if (!is_int($mrid) || $mrid <= 0) {
			throw new Exception('Invalid abonnement: $voorAbo =' . $mrid);
		}
		$repetitie = MaaltijdRepetitiesModel::instance()->getRepetitie($mrid);
		if (!$this->checkAanmeldFilter($uid, $repetitie->abonnement_filter)) {
			throw new Exception('Niet toegestaan vanwege aanmeldrestrictie: ' . $repetitie->abonnement_filter);
		}

		$aantal = 0;

        $maaltijden = MaaltijdenModel::instance()->find("mlt_repetitie_id = ? AND gesloten = false AND verwijderd = false AND datum >= ?", array($mrid, date('Y-m-d')));
        foreach ($maaltijden as $maaltijd) {
            if (!$this->existsByPrimaryKey(array($maaltijd->maaltijd_id, $uid))) {
                $this->aanmeldenDoorAbonnement($maaltijd->maaltijd_id, $mrid, $uid);
                $aantal++;
            }
        }
        return $aantal;
	}

}
