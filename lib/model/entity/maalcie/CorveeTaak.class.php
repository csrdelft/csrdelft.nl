<?php

/**
 * CorveeTaak.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * 
 * Een crv_taak instantie beschrijft een taak die een lid moet uitvoeren of (niet) uitgevoerd heeft als volgt:
 *  - uniek identificatienummer
 *  - welke functie deze taak inhoud (bijv. tafelpraeses)
 *  - welk lid deze taak uitvoerd
 *  - maaltijd waarmee deze taak verband houdt (optioneel)
 *  - datum en tijd waarop deze taak wordt uitgevoerd
 *  - aantal punten dat verdient kan worden
 *  - extra punten: bonus (positief) of malus (negatief) punten aantal
 *  - aantal punten dat is toegekend (exclusief bonus/malus)
 *  - aantal bonuspunten dat is toegekend
 *  - moment wanneer de punten zijn toegekend (datum + tijd)
 *  - of er een controle van de taak heeft plaatsgevonden (door de hyco) en zo ja of het ok was (anders null)
 * 
 * Het aanmaken van een corveetaak kan vanuit CorveeRepetitie gebeuren, maar ook vanuit MaaltijdCorvee bij het indelen van leden voor corvee-functies bij maaltijden; beide in verband met corvee-voorkeuren van leden, gewone danwel maaltijd-gerelateerde corvee-functies. (join Maaltijd.repetitie_id === MaaltijdCorvee.maaltijd_repetitie_id && join MaaltijdCorvee.corvee_repetitie_id === CorveeRepetitie.id)
 * De totale hoeveelheid punten van een lid zijn het puntenaantal van voorgaande jaren opgeslagen in lid.corvee_punten + de som van de toegekende punten van alle taken van een lid.
 * 
 * 
 * Zie ook MaaltijdCorvee.class.php
 * 
 */
class CorveeTaak implements Agendeerbaar {
	# primary key

	private $taak_id; # int 11
	private $functie_id; # foreign key crv_functie.id
	private $uid; # foreign key lid.uid
	private $crv_repetitie_id; # foreign key crv_repetitie.id
	private $maaltijd_id; # foreign key maaltijd.id
	private $datum; # date
	private $punten; # int 11
	private $bonus_malus; # int 11
	private $punten_toegekend; # int 11
	private $bonus_toegekend; # int 11
	private $wanneer_toegekend; # datetime
	private $wanneer_gemaild; # text
	private $verwijderd; # boolean

	public function __construct($tid = 0, $fid = 0, $uid = null, $crid = null, $mid = null, $datum = null, $punten = 0, $bonus_malus = 0, $toegekend = 0, $bonus_toegekend = 0, $wanneer = null, $gemaild = '', $verwijderd = false) {
		$this->taak_id = (int) $tid;
		$this->setFunctieId($fid);
		$this->setUid($uid);
		$this->setCorveeRepetitieId($crid);
		$this->setMaaltijdId($mid);
		if ($datum === null) {
			$datum = date('Y-m-d');
		}
		$this->setDatum($datum);
		$this->setPunten($punten);
		$this->setBonusMalus($bonus_malus);
		$this->setPuntenToegekend($toegekend);
		$this->setBonusToegekend($bonus_toegekend);
		$this->setWanneerToegekend($wanneer);
		$this->setWanneerGemaild($gemaild);
		$this->setVerwijderd($verwijderd);
	}

	public function getTaakId() {
		return (int) $this->taak_id;
	}

	public function getFunctieId() {
		return (int) $this->functie_id;
	}

	public function getUid() {
		return $this->uid;
	}

	public function getCorveeRepetitieId() {
		if (empty($this->crv_repetitie_id)) {
			return null;
		}
		return (int) $this->crv_repetitie_id;
	}

	public function getMaaltijdId() {
		if (empty($this->maaltijd_id)) {
			return null;
		}
		return (int) $this->maaltijd_id;
	}

	public function getDatum() {
		return $this->datum;
	}

	public function getPunten() {
		return (int) $this->punten;
	}

	public function getBonusMalus() {
		return (int) $this->bonus_malus;
	}

	public function getPuntenToegekend() {
		return (int) $this->punten_toegekend;
	}

	public function getBonusToegekend() {
		return (int) $this->bonus_toegekend;
	}

	public function getPuntenPrognose() {
		return $this->getPunten() + $this->getBonusMalus() - $this->getPuntenToegekend() - $this->getBonusToegekend();
	}

	public function getWanneerToegekend() {
		return $this->wanneer_toegekend;
	}

	public function getWanneerGemaild() {
		return $this->wanneer_gemaild;
	}

	public function getIsVerwijderd() {
		return (boolean) $this->verwijderd;
	}

	public function getLaatstGemaildTimestamp() {
		$pos = strpos($this->wanneer_gemaild, '&#013;');
		if ($pos === false) {
			return null;
		}
		return strtotime(substr($this->wanneer_gemaild, 0, $pos));
	}

	/**
	 * Berekent hoevaak er gemaild is op basis van wanneer er gemaild is.
	 * 
	 * @return int
	 */
	public function getAantalKeerGemaild() {
		return substr_count($this->wanneer_gemaild, '&#013;');
	}

	/**
	 * Bepaalt of er een herinnering gemaild moet worden op basis van het aantal verstuurde herinneringen en de ingestelde periode vooraf.
	 * 
	 * @return boolean
	 */
	public function getMoetHerinneren() {
		$aantal = $this->getAantalKeerGemaild();
		$datum = strtotime($this->getDatum());
		$laatst = $this->getLaatstGemaildTimestamp();
		$nu = strtotime(date('Y-m-d'));

		if ($laatst === $nu) {
			return false;
		}

		for ($i = intval(Instellingen::get('corvee', 'herinnering_aantal_mails')); $i > 0; $i--) {

			if ($aantal < $i &&
					$nu >= strtotime(Instellingen::get('corvee', 'herinnering_' . $i . 'e_mail'), $datum) &&
					$nu <= strtotime(Instellingen::get('corvee', 'herinnering_' . $i . 'e_mail_uiterlijk'), $datum)
			) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Bepaalt of er op tijd is gemaild op basis van de laatst verstuurde email.
	 * 
	 * @return boolean
	 */
	public function getIsTelaatGemaild() {
		$aantal = $this->getAantalKeerGemaild();
		$datum = strtotime($this->getDatum());
		$laatst = $this->getLaatstGemaildTimestamp();
		$nu = strtotime(date('Y-m-d'));
		$moeten = 0;

		for ($i = intval(Instellingen::get('corvee', 'herinnering_aantal_mails')); $i > 0; $i--) {
			$uiterlijk = strtotime(Instellingen::get('corvee', 'herinnering_' . $i . 'e_mail_uiterlijk'), $datum);
			if ($nu >= $uiterlijk) {
				$moeten++;
			}
			if ($aantal <= $i && $laatst >= $uiterlijk) {
				return true;
			}
		}
		if ($moeten > $aantal) {
			return true;
		}
		return false;
	}

	/**
	 * Lazy loading by foreign key.
	 * 
	 * @return CorveeFunctie
	 */
	public function getCorveeFunctie() {
		return FunctiesModel::get($this->functie_id);
	}

	public function setFunctieId($int) {
		if (!is_int($int)) {
			throw new Exception('Geen integer: functie id');
		}
		$this->functie_id = $int;
	}

	public function setUid($uid) {
		if ($uid !== null && !ProfielModel::existsUid($uid)) {
			throw new Exception('Geen lid: set lid id');
		}
		$this->uid = $uid;
	}

	public function setCorveeRepetitieId($int) {
		if ($int !== null && !is_int($int)) {
			throw new Exception('Geen integer: corvee-repetitie id');
		}
		$this->crv_repetitie_id = $int;
	}

	public function setMaaltijdId($int) {
		if ($int !== null && !is_int($int)) {
			throw new Exception('Geen integer: maaltijd id');
		}
		$this->maaltijd_id = $int;
	}

	public function setDatum($datum) {
		if (!is_string($datum)) {
			throw new Exception('Geen string: datum');
		}
		$this->datum = $datum;
	}

	public function setPunten($int) {
		if (!is_int($int)) {
			throw new Exception('Geen integer: punten');
		}
		$this->punten = $int;
	}

	public function setBonusMalus($int) {
		if (!is_int($int)) {
			throw new Exception('Geen integer: bonus malus');
		}
		$this->bonus_malus = $int;
	}

	public function setPuntenToegekend($int) {
		if (!is_int($int)) {
			throw new Exception('Geen integer: punten toegekend');
		}
		$this->punten_toegekend = $int;
	}

	public function setBonusToegekend($int) {
		if (!is_int($int)) {
			throw new Exception('Geen integer: bonus toegekend');
		}
		$this->bonus_toegekend = $int;
	}

	public function setWanneerToegekend($datumtijd) {
		if ($datumtijd !== null && !is_string($datumtijd)) {
			throw new Exception('Geen string: wanneer toegekend');
		}
		$this->wanneer_toegekend = $datumtijd;
	}

	public function setWanneerGemaild($datumtijd) {
		if (!is_string($datumtijd)) {
			throw new Exception('Geen string: wanneer gemaild');
		}
		if ($datumtijd !== '') {
			$datumtijd .= '&#013;' . $this->getWanneerGemaild();
		}
		$this->wanneer_gemaild = $datumtijd;
	}

	public function setVerwijderd($bool) {
		if (!is_bool($bool)) {
			throw new Exception('Geen boolean: verwijderd');
		}
		$this->verwijderd = $bool;
	}

	// Agendeerbaar ############################################################

	public function getUUID() {
		return $this->taak_id . '@corveetaak.csrdelft.nl';
	}

	public function getBeginMoment() {
		return strtotime($this->getDatum());
	}

	public function getEindMoment() {
		return $this->getBeginMoment();
	}

	public function getDuration() {
		return ($this->getEindMoment() - $this->getBeginMoment()) / 60;
	}

	public function getTitel() {
		if ($this->getUid()) {
			return $this->getCorveeFunctie()->naam . ' ' . ProfielModel::getNaam($this->getUid(), 'civitas');
		}
		return 'Corvee vacature (' . $this->getCorveeFunctie()->naam . ')';
	}

	public function getBeschrijving() {
		if ($this->getUid()) {
			return $this->getCorveeFunctie()->naam;
		}
		return 'Nog niet ingedeeld';
	}

	public function getLocatie() {
		return 'C.S.R. Delft';
	}

	public function getLink() {
		return '/corveerooster';
	}

	public function isHeledag() {
		return false;
	}

}

?>