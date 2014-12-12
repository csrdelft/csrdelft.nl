<?php

/**
 * MaaltijdAbonnement.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * 
 * Een mlt_abonnement instantie beschrijft een individueel abonnement van een lid voor een maaltijd-repetitie als volgt:
 *  - voor welke maaltijd-repetitie deze aanmelding is
 *  - van welk lid dit abonnement is
 * 
 * 
 * Bij het aanmaken van een abonnement (inschakelen) wordt het lid aangemeldt voor alle maaltijden waar dit abonnement voor geldt, die niet gesloten of verwijderd zijn. Daarom bevat maaltijd een foreign key mlt_repetitie.id.
 * Een abonnement wordt verwijderd als deze wordt uitgeschakeld. Het lid wordt dan afgemeld voor alle maaltijden waar dit abonnement voor geldt die niet gesloten of verwijderd zijn. Daarom bevat maaltijd-aanmelding een foreign key mlt_repetitie.id. Deze verwijzing is redundant, want dat kan ook uitgevonden worden via een join van aanmeldingen met de tabel maaltijden die ook een foreign key mlt_repetitie.id bevat, maar is wel erg handig.
 * 
 * Bijvoorbeeld:
 * Gebruiker heeft geen abonnement op donderdag-maaltijden, dus bij het aanmaken van een donderdag-maaltijd wordt de gebruiker niet autmatisch aangemeld.
 * Gebruiker meldt zich handmatig aan voor een specifieke donderdag-maaltijd.
 * Gebruiker schakelt abonnement in voor donderdag-maaltijden. Nu wordt de gebruiker voor alle bestaande (niet-gesloten) donderdag-maaltijden aangemeld.
 * De aanmelding bestaat al, dus wordt niet overschreven en het veld "door_abo" blijft dus NULL.
 * Gebruiker schakelt abonnement weer uit. Nu wordt de gebruiker voor alle bestaande (niet-gesloten) donderdag-maaltijden afgemeld waarvoor de gebruiker automatisch was aangemeld.
 * De handmatige aanmelding blijft dus bestaan en de gebruiker is nog steeds aangemeld voor die ene donderdag-maaltijd.
 * Dit is by design, als de handmatige aanmelding ook verwijderd moet worden bij het uitschakelen van het abonnement is dat een andere design mogelijkheid.
 * (Extreem eenvoudig aan te passen door bij het verwijderen van aanmeldingen niet te checken op door_abonnement.)
 * 
 * 
 * Zie ook MaaltijdAanmelding.class.php
 * 
 */
class MaaltijdAbonnement {
	# shared primary key

	private $mlt_repetitie_id; # foreign key mlt_repetitie.id
	private $uid; # foreign key lid.uid
	private $wanneer_ingeschakeld; # datetime
	private $maaltijd_repetitie;
	private $van_uid;
	private $waarschuwing;
	private $foutmelding;

	public function __construct($mrid = 0, $uid = '', $wanneer = '') {
		$this->mlt_repetitie_id = (int) $mrid;
		$this->uid = $uid;
		$this->setWanneerIngeschakeld($wanneer);
	}

	public function getMaaltijdRepetitieId() {
		return (int) $this->mlt_repetitie_id;
	}

	public function getUid() {
		return $this->uid;
	}

	public function getVanUid() {
		return $this->van_uid;
	}

	public function getWanneerIngeschakeld() {
		return $this->wanneer_ingeschakeld;
	}

	public function getMaaltijdRepetitie() {
		return $this->maaltijd_repetitie;
	}

	public function getWaarschuwing() {
		return $this->waarschuwing;
	}

	public function getFoutmelding() {
		return $this->foutmelding;
	}

	public function setWanneerIngeschakeld($datumtijd) {
		if (!is_string($datumtijd)) {
			throw new Exception('Geen string: wanneer ingeschakeld');
		}
		$this->wanneer_ingeschakeld = $datumtijd;
	}

	public function setMaaltijdRepetitie(MaaltijdRepetitie $repetitie) {
		$this->maaltijd_repetitie = $repetitie;
	}

	public function setVanUid($uid) {
		$this->van_uid = $uid;
	}

	public function setWaarschuwing($string) {
		if (!is_string($string)) {
			throw new Exception('Geen string: set waarschuwing');
		}
		$this->waarschuwing = $string;
	}

	public function setFoutmelding($string) {
		if (!is_string($string)) {
			throw new Exception('Geen string: set foutmelding');
		}
		$this->foutmelding = $string;
	}

}
