<?php

/**
 * CorveeVoorkeur.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * 
 * Een crv_voorkeur instantie beschrijft een voorkeur van een lid om een periodieke taak uit te voeren.
 * 
 * 
 * Zie ook CorveeRepetitie.class.php
 * 
 */
class CorveeVoorkeur {
	# shared primary key

	private $crv_repetitie_id; # foreign key crv_repetitie.id
	private $uid; # foreign key lid.uid
	private $corvee_repetitie;
	private $van_uid;

	public function __construct($crid = 0, $uid = '') {
		$this->crv_repetitie_id = (int) $crid;
		$this->uid = $uid;
	}

	public function getCorveeRepetitieId() {
		return (int) $this->crv_repetitie_id;
	}

	public function getUid() {
		return $this->uid;
	}

	public function getVanUid() {
		return $this->van_uid;
	}

	public function getCorveeRepetitie() {
		return $this->corvee_repetitie;
	}

	public function setCorveeRepetitie(CorveeRepetitie $repetitie) {
		$this->corvee_repetitie = $repetitie;
	}

	public function setVanUid($uid) {
		$this->van_uid = $uid;
	}

}
