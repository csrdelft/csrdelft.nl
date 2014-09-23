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
class Maaltijd implements Agendeerbaar {
	# primary key

	private $maaltijd_id; # int 11
	private $mlt_repetitie_id; # foreign key mlt_repetitie.id
	private $titel; # string 255
	private $aanmeld_limiet; # int 11
	private $datum; # date
	private $tijd; # time
	private $prijs; # float
	private $gesloten; # boolean
	private $laatst_gesloten; # int 11
	private $verwijderd; # boolean
	private $aanmeld_filter; # string 255
	private $aantal_aanmeldingen;
	private $archief;

	public function __construct($mid = 0, $mrid = null, $titel = '', $limiet = null, $datum = null, $tijd = null, $prijs = null, $gesloten = false, $wanneer_gesloten = null, $verwijderd = false, $filter = '') {
		$this->maaltijd_id = (int) $mid;
		if ($mrid !== null) {
			$this->mlt_repetitie_id = (int) $mrid;
		}
		$this->setTitel($titel);
		if ($limiet === null) {
			$limiet = intval(Instellingen::get('maaltijden', 'standaard_limiet'));
		}
		$this->setAanmeldLimiet($limiet);
		if ($datum === null) {
			$datum = date('Y-m-d');
		}
		$this->setDatum($datum);
		if ($tijd === null) {
			$tijd = Instellingen::get('maaltijden', 'standaard_aanvang');
		}
		$this->setTijd($tijd);
		if ($prijs === null) {
			$prijs = floatval(Instellingen::get('maaltijden', 'standaard_prijs'));
		}
		$this->setPrijs($prijs);
		$this->setGesloten($gesloten);
		$this->setLaatstGesloten($wanneer_gesloten);
		$this->setVerwijderd($verwijderd);
		$this->setAanmeldFilter($filter);
	}

	public function getMaaltijdId() {
		return (int) $this->maaltijd_id;
	}

	public function getMaaltijdRepetitieId() {
		if ($this->mlt_repetitie_id === null) {
			return null;
		}
		return (int) $this->mlt_repetitie_id;
	}

	public function getTitel() {
		return $this->titel;
	}

	public function getAanmeldLimiet() {
		return (int) $this->aanmeld_limiet;
	}

	public function getDatum() {
		return $this->datum;
	}

	public function getTijd() {
		return $this->tijd;
	}

	public function getPrijs() {
		return (float) $this->prijs;
	}

	public function getIsGesloten() {
		return (boolean) $this->gesloten;
	}

	public function getLaatstGesloten() {
		return $this->laatst_gesloten;
	}

	public function getIsVerwijderd() {
		return (boolean) $this->verwijderd;
	}

	public function getAanmeldFilter() {
		return $this->aanmeld_filter;
	}

	public function getAantalAanmeldingen() {
		return (int) $this->aantal_aanmeldingen;
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
		return ((float) ($this->getAantalAanmeldingen() + $this->getMarge())) * ($this->getPrijs() - floatval(Instellingen::get('maaltijden', 'budget_maalcie')));
	}

	public function getArchief() {
		return $this->archief;
	}

	public function setTitel($titel) {
		if (!is_string($titel)) {
			throw new Exception('Geen string: titel');
		}
		$this->titel = $titel;
	}

	public function setAanmeldLimiet($int) {
		if (!is_int($int) || $int < 0) {
			throw new Exception('Geen integer: aanmeld limiet');
		}
		$this->aanmeld_limiet = $int;
	}

	public function setDatum($datum) {
		if (!is_string($datum)) {
			throw new Exception('Geen string: datum');
		}
		$this->datum = $datum;
	}

	public function setTijd($time) {
		if (!is_string($time)) {
			throw new Exception('Geen string: tijd');
		}
		$this->tijd = $time;
	}

	public function setPrijs($prijs) {
		if (!is_float($prijs)) {
			throw new Exception('Geen float: prijs');
		}
		$this->prijs = $prijs;
	}

	public function setGesloten($bool) {
		if (!is_bool($bool)) {
			throw new Exception('Geen boolean: gesloten');
		}
		$this->gesloten = $bool;
	}

	public function setLaatstGesloten($datetime) {
		if ($datetime !== null && !is_string($datetime)) {
			throw new Exception('Geen string: laatst gesloten');
		}
		$this->laatst_gesloten = $datetime;
	}

	public function setVerwijderd($bool) {
		if (!is_bool($bool)) {
			throw new Exception('Geen boolean: verwijderd');
		}
		$this->verwijderd = $bool;
	}

	public function setAanmeldFilter($filter) {
		if (!is_string($filter)) {
			throw new Exception('Geen string: aanmeld filter');
		}
		$this->aanmeld_filter = $filter;
	}

	public function setAantalAanmeldingen($int) {
		if (!is_int($int) || $int < 0) {
			throw new Exception('Geen integer: aantal aanmeldingen');
		}
		$this->aantal_aanmeldingen = $int;
	}

	public function setArchief(ArchiefMaaltijd $archief) {
		$this->archief = $archief;
	}

	// Agendeerbaar ############################################################

	public function getUUID() {
		return $this->maaltijd_id . '@maaltijd.csrdelft.nl';
	}

	public function getBeginMoment() {
		return strtotime($this->getDatum() . ' ' . $this->getTijd());
	}

	public function getEindMoment() {
		return $this->getBeginMoment();
	}

	public function getDuration() {
		return ($this->getEindMoment() - $this->getBeginMoment()) / 60;
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

	public function magSluiten($uid) {
		// Er kunnen meerdere maaltijden op 1 dag zijn terwijl er maar 1 kookploeg is.
		// Een taak hoeft niet per se gekoppeld te zijn aan maaltijd en kan maximaal aan 1 maaltijd gekoppeld worden.
		$taken = CorveeTakenModel::getTakenVoorAgenda($this->getBeginMoment(), $this->getBeginMoment());
		foreach ($taken as $taak) {
			if ($taak->getUid() === $uid AND $taak->getCorveeFunctie()->maaltijd_sluiten) { // mag iemand met deze functie maaltijden sluiten ?
				return $taak; // de taak die toegang geeft tot de maaltijdlijst (wordt ook gebruikt voor tonen van link naar maaltijdlijst)
			}
		}
		if (opConfide() || LoginModel::mag('P_MAAL_MOD')) {
			return true;
		}
		return false;
	}

}
