<?php

/**
 * CorveeRepetitie.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * 
 * Een crv_repetitie instantie beschrijft een corvee taak die periodiek moet worden uitgevoerd als volgt:
 *  - uniek identificatienummer
 *  - bij welke maaltijdrepetitie deze periodieke taken horen (optioneel)
 *  - op welke dag van de week dit moet gebeuren
 *  - na hoeveel dagen dit opnieuw moet gebeuren
 *  - welke functie deze periodieke taak inhoud (bijv. kwalikok)
 *  - standaard aantal punten dat een lid krijgt voor deze periodieke taak
 *  - standaard aantal mensen dat deze periodieke taak moeten uitvoeren (bijv. 1 kwalikok, 2 hulpkoks, etc.)
 *  - of deze periodieke taak als voorkeur kan worden opgegeven (bijv. kwalikok is niet voorkeurbaar)
 * 
 * Bij het koppelen van corvee-repetities aan een maaltijd-repetitie maakt het mogelijk om bij het aanmaken van
 * een maaltijd automatisch ook corveetaken aan te maken.
 * Deze klasse weet dus welke en hoeveel corvee-functies er bij welke maaltijd-repetitie horen,
 * in verband met het later toewijzen van corvee-functies als taak aan een of meerdere leden.
 * Een maaltijd die los wordt aangemaakt, dus niet vanuit een maaltijd-repetitie, krijgt dus geen standaard corvee-taken.
 * Deze zullen met de hand moeten worden toegevoegd. Daarbij kan gebruik gemaakt worden van de dag van de week
 * van de maaltijd en te kijken naar de dag van de week van corvee-repetities.
 * Een lid kan een voorkeur aangeven voor een corvee-repetitie.
 * 
 * 
 * Zie ook CorveeTaak.class.php
 * 
 */
class CorveeRepetitie {
	# primary key

	private $crv_repetitie_id; # int 11
	private $mlt_repetitie_id; # foreign key mlt_repetitie.id
	private $dag_vd_week; # int 1
	private $periode_in_dagen; # int 11
	private $functie_id; # foreign key crv_functie.id
	private $standaard_punten; # int 11
	private $standaard_aantal; # int 11
	private $voorkeurbaar; # boolean

	public function __construct($crid = 0, $mrid = null, $dag = null, $periode = null, $fid = 0, $punten = 0, $aantal = null, $voorkeur = null) {
		$this->crv_repetitie_id = (int) $crid;
		$this->setMaaltijdRepetitieId($mrid);
		if ($dag === null) {
			$dag = intval(Instellingen::get('corvee', 'standaard_repetitie_weekdag'));
		}
		$this->setDagVanDeWeek($dag);
		if ($periode === null) {
			$periode = intval(Instellingen::get('corvee', 'standaard_repetitie_periode'));
		}
		$this->setPeriodeInDagen($periode);
		$this->setFunctieId($fid);
		$this->setStandaardPunten($punten);
		if ($aantal === null) {
			$aantal = intval(Instellingen::get('corvee', 'standaard_aantal_corveers'));
		}
		$this->setStandaardAantal($aantal);
		if ($voorkeur === null) {
			$voorkeur = (boolean) Instellingen::get('corvee', 'standaard_voorkeurbaar');
		}
		$this->setVoorkeurbaar($voorkeur);
	}

	public function getCorveeRepetitieId() {
		return (int) $this->crv_repetitie_id;
	}

	public function getMaaltijdRepetitieId() {
		if ($this->mlt_repetitie_id === null) {
			return null;
		}
		return (int) $this->mlt_repetitie_id;
	}

	/**
	 * 0: Sunday
	 * 6: Saturday
	 */
	public function getDagVanDeWeek() {
		return (int) $this->dag_vd_week;
	}

	public function getDagVanDeWeekText() {
		return strftime('%A', ($this->getDagVanDeWeek() + 3) * 24 * 3600);
	}

	public function getPeriodeInDagen() {
		return (int) $this->periode_in_dagen;
	}

	public function getPeriodeInDagenText() {
		switch ($this->getPeriodeInDagen()) {
			case 0: return '-';
			case 1: return 'elke dag';
			case 7: return 'elke week';
			default:
				if ($this->getPeriodeInDagen() % 7 === 0) {
					return 'elke ' . ($this->getPeriodeInDagen() / 7) . ' weken';
				} else {
					return 'elke ' . $this->getPeriodeInDagen() . ' dagen';
				}
		}
	}

	public function getFunctieId() {
		return (int) $this->functie_id;
	}

	public function getStandaardPunten() {
		return (int) $this->standaard_punten;
	}

	public function getStandaardAantal() {
		return (int) $this->standaard_aantal;
	}

	public function getIsVoorkeurbaar() {
		return (boolean) $this->voorkeurbaar;
	}

	/**
	 * Lazy loading by foreign key.
	 * 
	 * @return CorveeFunctie
	 */
	public function getCorveeFunctie() {
		return FunctiesModel::get($this->functie_id);
	}

	public function setMaaltijdRepetitieId($mrid) {
		if ($mrid !== null && !is_int($mrid)) {
			throw new Exception('Ongeldig id: maaltijd repetitie');
		}
		$this->mlt_repetitie_id = $mrid;
	}

	public function setDagVanDeWeek($int) {
		if (!is_int($int) || $int < 0 || $int > 6) {
			throw new Exception('Geen integer: dag van de week');
		}
		$this->dag_vd_week = $int;
	}

	public function setPeriodeInDagen($int) {
		if (!is_int($int) || $int < 0) {
			throw new Exception('Geen integer: periode in dagen');
		}
		$this->periode_in_dagen = $int;
	}

	public function setFunctieId($int) {
		if (!is_int($int)) {
			throw new Exception('Geen integer: functie id');
		}
		$this->functie_id = $int;
	}

	public function setStandaardPunten($int) {
		if (!is_int($int) || $int < 0) {
			throw new Exception('Geen integer: standaard punten');
		}
		$this->standaard_punten = $int;
	}

	public function setStandaardAantal($int) {
		if (!is_int($int) || $int < 0) {
			throw new Exception('Geen integer: standaard aantal');
		}
		$this->standaard_aantal = $int;
	}

	public function setVoorkeurbaar($bool) {
		if (!is_bool($bool)) {
			throw new Exception('Geen boolean: voorkeurbaar');
		}
		$this->voorkeurbaar = $bool;
	}

}

?>