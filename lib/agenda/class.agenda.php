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
 * Een pils voor degene die er een betere naam voor verzint.
 */
interface Agendeerbaar {
	
	public function getMoment();
	public function getTitel();
	public function getBeschrijving();	
}

/**
 * AgendaItems zijn dingen in de agenda die niet ergens anders uit de
 * webstek komen.
 */
class AgendaItem implements Agendeerbaar {
	
	private $itemid;
	private $moment;
	private $titel;
	private $beschrijving;
	
	public function __construct(){
		
	}
	
	public function getItemID(){
		return $this->itemid;
	}
	public function getMoment(){
		return $this->moment;
	}
	public function getTitel(){
		return $this->titel;
	}
	public function getBeschrijving(){
		return $this->beschrijving;
	}
	
	public function setMoment($moment){
		$this->moment=$moment;
	}
	public function setTitel($titel){
		$this->titel=$titel;
	}
	public function setBeschrijving($beschrijving){
		$this->beschrijving=$beschrijving;
	}
}

class Agenda {
	
}
?>