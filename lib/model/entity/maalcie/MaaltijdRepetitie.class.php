<?php

/**
 * MaaltijdRepetitie.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * 
 * Een mlt_repetitie instantie beschrijft een maaltijd die periodiek wordt gehouden als volgt:
 *  - uniek identificatienummer
 *  - op welke dag van de week de maaltijd wordt gehouden
 *  - na hoeveel dagen deze opnieuw wordt gehouden
 *  - de standaard naam van de maaltijd (bijv. donderdag-maaltijd)
 *  - de standaard tijd van de maaltijd (bijv. 18:00)
 *  - of er een abonnement kan worden genomen op deze periodieke maaltijden
 *  - de standaard limiet van het aantal aanmeldingen
 *  - of er restricties gelden voor wie zich mag abonneren op deze maaltijd
 * 
 * 
 * De standaard titel, limiet en filter worden standaard overgenomen, maar kunnen worden overschreven per maaltijd.
 * Bij het aanmaken van een nieuwe maaltijd (op basis van deze repetitie) worden alle leden met een abonnement op deze repetitie aangemeldt voor deze nieuwe maaltijd.
 * 
 * 
 * Zie ook MaaltijdAbonnement.class.php
 * 
 */
class MaaltijdRepetitie {
	# primary key

	private $mlt_repetitie_id; # int 11
	private $dag_vd_week; # int 1
	private $periode_in_dagen; # int 11
	private $standaard_titel; # string 255
	private $standaard_tijd; # time
	private $standaard_prijs; # double
	private $abonneerbaar; # boolean
	private $standaard_limiet; # int 11
	private $abonnement_filter; # string 255

	public function __construct($mrid = 0, $dag = null, $periode = null, $titel = '', $tijd = null, $prijs = null, $abo = null, $limiet = null, $filter = null) {
		$this->mlt_repetitie_id = (int) $mrid;
		if ($dag === null) {
			$dag = intval(Instellingen::get('maaltijden', 'standaard_repetitie_weekdag'));
		}
		$this->setDagVanDeWeek($dag);
		if ($periode === null) {
			$periode = intval(Instellingen::get('maaltijden', 'standaard_repetitie_periode'));
		}
		$this->setPeriodeInDagen($periode);
		$this->setStandaardTitel($titel);
		if ($tijd === null) {
			$tijd = Instellingen::get('maaltijden', 'standaard_aanvang');
		}
		$this->setStandaardTijd($tijd);
		if ($prijs === null) {
			$prijs = intval(Instellingen::get('maaltijden', 'standaard_prijs'));
		}
		$this->setStandaardPrijs($prijs);
		if ($abo === null) {
			$abo = (boolean) Instellingen::get('maaltijden', 'standaard_abonneerbaar');
		}
		$this->setAbonneerbaar($abo);
		if ($limiet === null) {
			$limiet = intval(Instellingen::get('maaltijden', 'standaard_limiet'));
		}
		$this->setStandaardLimiet($limiet);
		$this->setAbonnementFilter($filter);
	}

	public function getMaaltijdRepetitieId() {
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

	public function getStandaardTitel() {
		return $this->standaard_titel;
	}

	public function getStandaardTijd() {
		return $this->standaard_tijd;
	}

	public function getStandaardPrijs() {
		return (int) $this->standaard_prijs;
	}

	public function getStandaardPrijsFloat() {
		return (float) $this->getStandaardPrijs() / 100.0;
	}

	public function getIsAbonneerbaar() {
		return (boolean) $this->abonneerbaar;
	}

	public function getStandaardLimiet() {
		return (int) $this->standaard_limiet;
	}

	public function getAbonnementFilter() {
		return $this->abonnement_filter;
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

	public function setStandaardTitel($titel) {
		if (!is_string($titel)) {
			throw new Exception('Geen string: standaard titel');
		}
		$this->standaard_titel = $titel;
	}

	public function setStandaardTijd($time) {
		if (!is_string($time)) {
			throw new Exception('Geen string: standaard tijd');
		}
		$this->standaard_tijd = $time;
	}

	public function setStandaardPrijs($prijs) {
		if (!is_int($prijs)) {
			throw new Exception('Geen integer: standaard prijs: ' . $prijs);
		}
		$this->standaard_prijs = $prijs;
	}

	public function setAbonneerbaar($bool) {
		if (!is_bool($bool)) {
			throw new Exception('Geen boolean: abonneerbaar');
		}
		$this->abonneerbaar = $bool;
	}

	public function setStandaardLimiet($int) {
		if (!is_int($int) || $int < 0) {
			throw new Exception('Geen integer: standaard limiet');
		}
		$this->standaard_limiet = $int;
	}

	public function setAbonnementFilter($filter) {
		if (!is_string($filter) AND $filter !== null) {
			throw new Exception('Geen string: abonnement filter');
		}
		$this->abonnement_filter = $filter;
	}

}

?>