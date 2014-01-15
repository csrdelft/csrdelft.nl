<?php

/**
 * CorveeFunctie.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * 
 * Een crv_functie instantie beschrijft een functie die een lid kan uitvoeren als taak en of hiervoor een kwalificatie nodig is.
 * Zo ja, dan moet een lid op moment van toewijzen van de taak over deze kwalificatie beschikken (lid.id moet voorkomen in tabel crv_kwalificaties).
 * 
 * Bijvoorbeeld:
 *  - Tafelpraeses
 *  - Kwalikok (kwalificatie benodigd!)
 *  - Afwasser
 *  - Keuken/Afzuigkap/Frituur schoonmaker
 *  - Klusser
 * 
 * Standaard punten wordt standaard overgenomen, maar kan worden overschreven per corveetaak.
 * 
 * 
 * Zie ook CorveeKwalificatie.class.php en CorveeTaak.class.php
 * 
 */
class CorveeFunctie {

	# primary key
	private $functie_id; # int 11
	
	private $naam; # string 255
	private $afkorting; # string 11
	private $email_bericht; # text
	private $standaard_punten; # int 11
	private $kwalificatie_benodigd; # boolean
	
	private $gekwalificeerden;
	
	public function __construct($fid=0, $naam='', $afk='', $email='', $punten=0, $kwali=null) {
		$this->functie_id = (int) $fid;
		$this->setNaam($naam);
		$this->setAfkorting($afk);
		$this->setEmailBericht($email);
		$this->setStandaardPunten($punten);
		if ($kwali === null) {
			$kwali = (bool) Instellingen::get('corvee', 'standaard_kwalificatie');
		}
		$this->setKwalificatieBenodigd($kwali);
	}
	
	public function getFunctieId() {
		return (int) $this->functie_id;
	}
	
	public function getNaam() {
		return $this->naam;
	}
	public function getAfkorting() {
		return $this->afkorting;
	}
	public function getEmailBericht() {
		return $this->email_bericht;
	}
	public function getStandaardPunten() {
		return (int) $this->standaard_punten;
	}
	public function getIsKwalificatieBenodigd() {
		return (bool) $this->kwalificatie_benodigd;
	}
	
	public function getGekwalificeerden() {
		return $this->gekwalificeerden;
	}
	
	public function setNaam($string) {
		if (!is_string($string)) {
			throw new Exception('Geen string: functie naam');
		}
		$this->naam = $string;
	}
	public function setAfkorting($string) {
		if (!is_string($string)) {
			throw new Exception('Geen string: functie afkorting');
		}
		$this->afkorting = $string;
	}
	public function setEmailBericht($string) {
		if (!is_string($string)) {
			throw new Exception('Geen string: email bericht');
		}
		$this->email_bericht = $string;
	}
	public function setStandaardPunten($int) {
		if (!is_int($int)) {
			throw new Exception('Geen integer: standaard punten');
		}
		$this->standaard_punten = $int;
	}
	public function setKwalificatieBenodigd($bool) {
		if (!is_bool($bool)) {
			throw new Exception('Geen boolean: kwalificatie benodigd');
		}
		$this->kwalificatie_benodigd = $bool;
	}
	
	public function setGekwalificeerden($kwali) {
		if ($kwali !== null && !is_array($kwali)) {
			throw new Exception('Geen array: gekwalificeerden');
		}
		$this->gekwalificeerden = $kwali;
	}
}

?>