<?php

/**
 * CorveeVrijstelling.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * 
 * Een crv_vrijstelling instantie bevat het volgende per lid:
 *  - begindatum van de periode waarvoor de vrijstelling geldt
 *  - einddatum van de periode waarvoor de vrijstelling geldt
 *  - percentage van de corveepunten die in een jaar gehaald dienen te worden
 * 
 * Wordt gebruikt bij de indeling van corveetaken om bijv. leden die
 * in het buitenland zitten niet in te delen gedurende die periode.
 * 
 */
class CorveeVrijstelling {
	# primary key

	private $uid; # foreign key lid.uid
	private $begin_datum; # date
	private $eind_datum; # date
	private $percentage; # int 3

	public function __construct($uid = null, $begin = null, $eind = null, $percentage = null) {
		$this->uid = $uid;
		if ($begin === null) {
			$begin = date('Y-m-d');
		}
		$this->setBeginDatum($begin);
		if ($eind === null) {
			$eind = date('Y-m-d');
		}
		$this->setEindDatum($eind);
		if ($percentage === null) {
			$percentage = intval(Instellingen::get('corvee', 'standaard_vrijstelling_percentage'));
		}
		$this->setPercentage($percentage);
	}

	public function getUid() {
		return $this->uid;
	}

	public function getBeginDatum() {
		return $this->begin_datum;
	}

	public function getEindDatum() {
		return $this->eind_datum;
	}

	public function getPercentage() {
		return (int) $this->percentage;
	}

	public function getPunten() {
		return (int) ceil($this->getPercentage() * intval(Instellingen::get('corvee', 'punten_per_jaar')) / 100);
	}

	public function setBeginDatum($datum) {
		if (!is_string($datum)) {
			throw new Exception('Geen string: begin datum');
		}
		$this->begin_datum = $datum;
	}

	public function setEindDatum($datum) {
		if (!is_string($datum)) {
			throw new Exception('Geen string: eind datum');
		}
		$this->eind_datum = $datum;
	}

	public function setPercentage($int) {
		if (!is_int($int) || $int < intval(Instellingen::get('corvee', 'vrijstelling_percentage_min')) || $int > intval(Instellingen::get('corvee', 'vrijstelling_percentage_max'))) {
			throw new Exception('Geen integer: percentage');
		}
		$this->percentage = $int;
	}

}

?>