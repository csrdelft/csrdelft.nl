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
	private $rechtenBekijken;

	public function __construct($itemid, $beginMoment, $eindMoment, $titel, $beschrijving, $rechtenBekijken) {
		$this->itemid = $itemid;
		$this->setBeginMoment($beginMoment);
		$this->setEindMoment($eindMoment);
		$this->setTitel($titel);
		$this->setBeschrijving($beschrijving);
		$this->setRechtenBekijken($rechtenBekijken);
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
	public function getRechtenBekijken() {
		return $this->getRechtenBekijken();
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
	public function setRechtenBekijken($rechtenBekijken) {
		$this->rechtenBekijken = $rechtenBekijken;
	}

	public function magBekijken() {
		return LoginLid::instance()->getLid()->hasPermission($this->getRechtenBekijken());
	}
}

/**
 * De Agenda bevat alle Agendeerbare objecten die voorkomen in de webstek.
 */
class Agenda {

	private $items;

	public function __construct() {

	}

	public function getItems($van=null, $tot=null, $filter=false) {
		$result = array();

		$qItems = "SELECT id, titel, beschrijving, begin, eind, rechtenBekijken FROM agenda WHERE 1=1";
		if ($van != null) {
			$qItems .= " AND eind >= '".$van."'";
		}
		if ($tot != null) {
			$qItems .= " AND begin <= '".$tot."'";
		}
		$qItems .= " ORDER BY begin ASC, titel ASC";

		$rItems = MySql::instance()->query($qItems);
		while ($aItem = MySql::instance()->next($rItems)) {
			$item = new AgendaItem($aItem['id'], strtotime($aItem['begin']), strtotime($aItem['eind']), $aItem['titel'], $aItem['beschrijving'], $aItem['rechtenBekijken']);

			if ($filter == false || $item->magBekijken()) {
				$result[] = $item;
			}
		}

		return $result;
	}

	public function getItemsByWeek($jaar=null, $week=null) {
		$van = null;
		$tot = null;

		return $this->getItems($van, $tot);
	}

	public function getItemsByMaand($jaar=null, $maand=null) {		
		// Zondag van de eerste week van de maand uitrekenen
		$startMoment = mktime(0, 0, 0, $maand, 1, $jaar);		
		if (date('w', $startMoment) != 0) {
			$startMoment = strtotime('last Sunday', $startMoment);
		}
		
		// Zaterdag van de laatste week van de maand uitrekenen
		$eindMoment = mktime(0, 0, 0, $maand, 1, $jaar);
		$eindMoment = strtotime('-1 second', strtotime('+1 month', $eindMoment));
		if (date('w', $eindMoment) != 6) {
			$eindMoment = strtotime('next Saturday', $eindMoment);
		}
		
		// Array met weken en dagen maken
		$cur = $startMoment;	
		$agenda = array();
		while ($cur != $eindMoment+1) {
			$week = strftime('%U', $cur);
			$dag = date('d', $cur);
			
			$agenda[$week][$dag] = array();
			
			$cur = strtotime('+1 day', $cur);
		}
		
		// Items toevoegen aan het array
		$items = $this->getItems(date('Y-m-d', $startMoment), date('Y-m-d', $eindMoment));
		foreach ($items as $item) {
			$week = strftime('%U', $item->getBeginMoment());
			$dag = date('d', $item->getEindMoment());
			$agenda[$week][$dag][] = $item;
		}	
		
		return $agenda;
	}
}
?>