<?php


# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agenda.php
# -------------------------------------------------------------------
# Dataklassen voor de agenda.
# -------------------------------------------------------------------

/**
 * Dit is een interface dat geïmplementeerd kan worden in allerlei 
 * klassen, die dan als item in de agenda kunnen verschijnen.
 */
interface Agendeerbaar {

	public function getBeginMoment();
	public function getEindMoment();
	public function getTitel();
	public function getBeschrijving();
}

/**
 * AgendaItems zijn dingen in de agenda die niet ergens anders uit de
 * webstek komen.
 */
class AgendaItem implements Agendeerbaar {

	private $itemid;
	private $beginMoment;
	private $eindMoment;
	private $titel;
	private $beschrijving;

	public function __construct() {

	}

	public function getItemID() {
		return $this->itemid;
	}
	public function getBeginMoment() {
		return $this->beginMoment;
	}
	public function getEindMoment() {
		return $this->eindMoment;
	}
	public function getTitel() {
		return $this->titel;
	}
	public function getBeschrijving() {
		return $this->beschrijving;
	}

	public function setBeginMoment($beginMoment) {
		$this->beginMoment = $beginMoment;
	}
	public function setEindMoment($eindMoment) {
		$this->eindMoment = $eindMoment;
	}
	public function setTitel($titel) {
		$this->titel = $titel;
	}
	public function setBeschrijving($beschrijving) {
		$this->beschrijving = $beschrijving;
	}
}

/**
 * De Agenda bevat alle Agendeerbare objecten die voorkomen in de webstek.
 */
class Agenda {
	
	private $items;
	
	public function __construct() {
		
	}
}
?>