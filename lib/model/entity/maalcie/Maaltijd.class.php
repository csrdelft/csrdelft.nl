<?php

/**
 * Maaltijd.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * 
 * Een mlt_maaltijd instantie beschrijft een individuele maaltijd als volgt:
 *  - uniek identificatienummer
 *  - door welke repetitie deze maaltijd is aangemaakt (optioneel)
 *  - titel (bijv. Donderdagmaaltijd)
 *  - limiet op het aantal aanmeldingen
 *  - datum en tijd waarop de maaltijd plaatsvind (op basis van vandaag en/of repetitie.dag_vd_week en repetitie.periode)
 *  - of de maaltijd gesloten is voor aanmeldingen en afmeldingen
 *  - moment wanneer de maaltijd voor het laatst is gesloten (gebeurt in principe maar 1 keer)
 *  - of de maaltijd verwijderd is (in de prullenbak zit)
 *  - of er restricties gelden voor wie zich mag aanmelden
 * 
 * Een gesloten maaltijd kan weer heropend worden.
 * Een verwijderde maaltijd kan weer uit de prullenbak worden gehaald.
 * Zolang een maaltijd verwijderd is doet en telt deze niet meer mee in het maalcie-systeem.
 * Als de restricties gewijzigt worden nadat er al aangemeldingen zijn (direct na het aanmaken van een maaltijd vanwege abonnementen) worden illegale aanmeldingen automatisch verwijderd.
 * In principe worden maaltijden aangemaakt vanuit maaltijd-repetitie in verband met maaltijd-corvee-taken en corvee-voorkeuren van leden.
 * 
 * 
 * Zie ook MaaltijdAanmelding.class.php
 * 
 */
class Maaltijd extends PersistentEntity implements Agendeerbaar {
	# primary key

	public $maaltijd_id; # int 11
	public $mlt_repetitie_id; # foreign key mlt_repetitie.id
	public $titel; # string 255
	public $aanmeld_limiet; # int 11
	public $datum; # date
	public $tijd; # time
	public $prijs; # int 11
	public $gesloten = false; # boolean
	public $laatst_gesloten; # int 11
	public $verwijderd = false; # boolean
	public $aanmeld_filter; # string 255
	public $omschrijving; # text
	public $aantal_aanmeldingen;
	public $archief;
	/**
	 * De taak die rechten geeft voor het bekijken en sluiten van de maaltijd(-lijst)
	 * @var CorveeTaak 
	 */
	public $maaltijdcorvee;

	public function getPrijsFloat() {
        return (float) $this->prijs / 100.0;
    }

    /**
     * @return int
     */
    public function getAantalAanmeldingen() {
        $aantal = MaaltijdAanmeldingenModel::instance()->select(array('SUM(aantal_gasten) + COUNT(*)'), 'maaltijd_id = ?', array($this->maaltijd_id));
        return (int) $aantal->fetchColumn();
    }

	/**
	 * Bereken de marge in verband met niet aangemelde gasten.
	 * 
	 * @return int
	 */
	public function getMarge() {
		$aantal = $this->getAantalAanmeldingen();
		$marge = floor($aantal / floatval(Instellingen::get('maaltijden', 'marge_gasten_verhouding')));
		$min = intval(Instellingen::get('maaltijden', 'marge_gasten_min'));
		if ($marge < $min) {
			$marge = $min;
		}
		$max = intval(Instellingen::get('maaltijden', 'marge_gasten_max'));
		if ($marge > $max) {
			$marge = $max;
		}
		return $marge;
	}

	/**
	 * Bereken het budget voor deze maaltijd.
	 * 
	 * @return double
	 */
	public function getBudget() {
		$budget = $this->getAantalAanmeldingen() + $this->getMarge();
		$budget *= $this->prijs - intval(Instellingen::get('maaltijden', 'budget_maalcie'));
		return floatval($budget) / 100.0;
	}

	// Agendeerbaar ############################################################

    public function getTitel() {
        return $this->titel;
    }

    public function getBeginMoment() {
		return strtotime($this->datum . ' ' . $this->tijd);
	}

	public function getEindMoment() {
		return $this->getBeginMoment() + 7200;
	}

	public function getBeschrijving() {
		return 'Maaltijd met ' . $this->getAantalAanmeldingen() . ' eters';
	}

	public function getLocatie() {
		return 'C.S.R. Delft';
	}

	public function getLink() {
		return '/maaltijden';
	}

	public function isHeledag() {
		return false;
	}

	// Controller ############################################################

	/**
	 * Deze functie bepaalt of iemand de maaltijd(-lijst) mag zien.
	 * 
	 * @param string $uid
	 * @return boolean
	 */
	public function magBekijken($uid) {
		if (!isset($this->maaltijdcorvee)) {
			// Zoek op datum, want er kunnen meerdere maaltijden op 1 dag zijn terwijl er maar 1 kookploeg is.
			// Ook hoeft een taak niet per se gekoppeld te zijn aan een maaltijd (maximaal aan 1 maaltijd).
			$taken = CorveeTakenModel::getTakenVoorAgenda($this->getBeginMoment(), $this->getBeginMoment());
			foreach ($taken as $taak) {
				if ($taak->getUid() === $uid AND $taak->getMaaltijdId() !== null) { // checken op gekoppelde maaltijd (zie hierboven)
					$this->maaltijdcorvee = $taak; // de taak die toegang geeft tot de maaltijdlijst
					return true;
				}
			}
			$this->maaltijdcorvee = false;
		}
		return $this->maaltijdcorvee !== false;
	}

	/**
	 * Deze functie bepaalt of iemand deze maaltijd mag sluiten of niet.
	 * 
	 * @param string $uid
	 * @return boolean
	 */
	public function magSluiten($uid) {
		return $this->magBekijken($uid) AND $this->maaltijdcorvee->getCorveeFunctie()->maaltijden_sluiten; // mag iemand met deze functie maaltijden sluiten?
	}

    protected static $table_name = 'mlt_maaltijden';
    protected static $persistent_attributes = array(
        'maaltijd_id' => array(T::Integer, false, 'auto_increment'),
        'mlt_repetitie_id' => array(T::Integer, true),
        'titel' => array(T::String),
        'aanmeld_limiet' => array(T::Integer),
        'datum' => array(T::Date),
        'tijd' => array(T::Time),
        'prijs' => array(T::Integer),
        'gesloten' => array(T::Boolean),
        'laatst_gesloten' => array(T::Integer, true),
        'verwijderd' => array(T::Boolean),
        'aanmeld_filter' => array(T::String, true),
        'omschrijving' => array(T::Text, true),
    );

    protected static $primary_key = array('maaltijd_id');

    /**
     * De API voor de app gebruikt json_encode
     *
     * @return array|mixed
     */
    public function jsonSerialize() {
        $json = parent::jsonSerialize();
        $json['tijd'] = date('G:i', strtotime($json['tijd']));
        $json['aantal_aanmeldingen'] = $this->getAantalAanmeldingen();
        $json['gesloten'] = $json['gesloten'] ? '1' : '0';
        $json['prijs'] = strval($json['prijs']);
        return $json;
    }

}
