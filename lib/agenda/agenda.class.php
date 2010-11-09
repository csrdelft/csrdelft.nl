<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agenda.php
# -------------------------------------------------------------------
# Dataklassen voor de agenda.
# -------------------------------------------------------------------

require_once 'maaltijden/maaltrack.class.php';

/**
 * Dit is een interface dat geïmplementeerd kan worden in allerlei
 * klassen, die dan als item in de agenda kunnen verschijnen.
 */
interface Agendeerbaar {

	public function getBeginMoment(); //timestamp van beginmoment
	public function getEindMoment();  //timestamp van eindmoment
	public function getTitel();
	public function getBeschrijving();
	public function isHeledag();

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

	public function __construct($itemid=0, $beginMoment=0, $eindMoment=0, $titel='', $beschrijving='', $rechtenBekijken='P_NOBODY') {
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
		return $this->rechtenBekijken;
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
		return LoginLid::instance()->hasPermission($this->getRechtenBekijken());
	}
	//lekker fies
	public function isHeledag(){
		return 
			date('H:i', $this->getBeginMoment())=='00:00' AND
			date('H:i', $this->getEindMoment())=='23:59';
	}
	
	public function opslaan() {
		$db = MySql::instance();
		if ($this->getItemID() == 0) {
			$query = "
				INSERT INTO agenda (
					titel, beschrijving, begin, eind, rechtenBekijken
				) VALUES (
					'".$db->escape($this->getTitel())."',
					'".$db->escape($this->getBeschrijving())."',
					FROM_UNIXTIME(".$this->getBeginMoment()."),
					FROM_UNIXTIME(".$this->getEindMoment()."),
					'".$this->getRechtenBekijken()."'
				);";
		} else {
			$query = "
				UPDATE agenda SET
					titel = '".$db->escape($this->getTitel())."',
					beschrijving = '".$db->escape($this->getBeschrijving())."',
					begin = FROM_UNIXTIME(".$this->getBeginMoment()."),
					eind = FROM_UNIXTIME(".$this->getEindMoment().")
				WHERE id=".$this->getItemID().";";
		}
		if ($db->query($query)) {
			if ($this->getItemID() == 0) {
				$this->itemid = $db->insert_id();
			}
			return true;
		}
		return false;
	}
	
	public function verwijder() {
		$db = MySQL::instance();
		$query = "DELETE FROM agenda WHERE id = ".$this->getItemID();
		if ($db->query($query)) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function getItem($id) {
		$db = MySQL::instance();
		$query = "SELECT titel, beschrijving, begin, eind, rechtenBekijken 
					FROM agenda WHERE id = ".(int)$id;
		$item = $db->getRow($query);
		$item['begin'] = strtotime($item['begin']);
		$item['eind'] = strtotime($item['eind']);
		
		return new AgendaItem($id, $item['begin'], $item['eind'], $item['titel'], 
				$item['beschrijving'], $item['rechtenBekijken']);
	}
}

/**
 * De Agenda bevat alle Agendeerbare objecten die voorkomen in de webstek.
 */
class Agenda {

	private $items;

	public function __construct() {

	}
	
	public function magToevoegen() {
		return LoginLid::instance()->hasPermission('P_AGENDA_POST');
	}
	
	public function magBeheren() {
		return LoginLid::instance()->hasPermission('P_AGENDA_MOD');
	}

	public function getItems($van=null, $tot=null, $filter) {
		$result = array();

		// Regulie agenda-items
		$qItems = "SELECT id, titel, beschrijving, begin, eind, rechtenBekijken FROM agenda WHERE 1=1";
		if ($van != null) {
			$qItems .= " AND eind >= '".date('Y-m-d', $van)."'";
		}
		if ($tot != null) {
			$qItems .= " AND begin <= '".date('Y-m-d', $tot)."'";
		}
		$qItems .= " ORDER BY begin ASC, titel ASC";

		$rItems = MySql::instance()->query($qItems);
		while ($aItem = MySql::instance()->next($rItems)) {
			$item = new AgendaItem($aItem['id'], strtotime($aItem['begin']), strtotime($aItem['eind']), $aItem['titel'], $aItem['beschrijving'], $aItem['rechtenBekijken']);

			if ($filter == false || $item->magBekijken()) {
				$result[] = $item;
			}
		}
		
		if(Instelling::get('agenda_toonMaaltijden')=='ja'){
			// Maaltijden ophalen
			$maaltrack = new Maaltrack();
			// Ranzige hack met $van+1, anders neemt de maaltijdketzer de huidige tijd
			$result = array_merge($result, $maaltrack->getMaaltijden($van+1, $tot, $filter, true, null, false));
		}
		if(Instelling::get('agenda_toonVerjaardagen')=='ja'){
			//Verjaardagen. Omdat Leden eigenlijk niet Agendeerbaar, maar meer iets als
			//PeriodiekAgendeerbaar zijn, maar we geen zin hebben om dat te implementeren,
			//doen we hier even een vieze hack waardoor
			$GLOBALS['agenda_jaar']=date('Y', $van);
			$GLOBALS['agenda_maand']=date('m', ($van+$tot/2));
			$result = array_merge($result, Lid::getVerjaardagen($van, $tot));
		}
		
		// Sorteren
		usort($result, array('Agenda', 'vergelijkAgendeerbaars'));

		return $result;
	}

	public function getItemsByWeek($jaar=null, $week=null) {
		$van = null;
		$tot = null;

		return $this->getItems($van, $tot);
	}
	public function getItemsByDay($jaar, $maand, $dag){
		$van=mktime(0, 0, 0, $maand, $dag, $jaar);
		$tot=mktime(0, 0, 0, $maand, $dag+1, $jaar);
		
		return $this->getItems($van, $tot, true);
	}
	public function getItemsByMaand($jaar, $maand, $filter) {
		// Zondag van de eerste week van de maand uitrekenen
		$startMoment = mktime(0, 0, 0, $maand, 1, $jaar);
		if (date('w', $startMoment) != 0) {
			$startMoment = strtotime('last Sunday', $startMoment);
		}
		
		// Zaterdag van de laatste week van de maand uitrekenen
		$eindMoment = mktime(0, 0, 0, $maand, 1, $jaar);
		$eindMoment = strtotime('+1 month', $eindMoment) - 1;
		if (date('w', $eindMoment) == 6) {
			$eindMoment++;			
		} else {
			$eindMoment = strtotime('next Saturday', $eindMoment);
			$eindMoment = strtotime('+1 day', $eindMoment);
		}
		
		// Array met weken en dagen maken
		$cur = $startMoment;		
		$agenda = array();
		while ($cur != $eindMoment) {
			$week = Agenda::weekNumber($cur);
			$dag = date('d', $cur);			
			$agenda[$week][$dag]['datum'] = $cur;
			$agenda[$week][$dag]['items'] = array();
			
			$cur = strtotime('+1 day', $cur);			
		}
				
		// Items toevoegen aan het array
		$items = $this->getItems($startMoment, $eindMoment, $filter);
		foreach ($items as $item) {
			$week = Agenda::weekNumber($item->getBeginMoment());
			$dag = date('d', $item->getEindMoment());
			$agenda[$week][$dag]['items'][] = $item;
		}	
		
		return $agenda;
	}
	/*
	 * Zoek in de activiteiten (titel en beschrijving) van vandaag
	 * naar het woord $woord, geef de eerste terug.
	 */
	public function isActiviteitGaande($woord){
		foreach($this->getItemsByDay(date('Y'), date('m'), date('d')) as $item){
			if(	stristr($item->getTitel(), $woord)!==false OR
				stristr($item->getBeschrijving(), $woord)!==false ){
				return $item;
				break;
			}
		}
		return null;
	}
	
	/**
	 * Geeft het weeknummer van de eerste dag van de week van $date terug.
	 */
	public static function weekNumber($date) {
		if (date('w', $date) == 0) {
			return strftime('%U', $date);
		} else {
			return strftime('%U', strtotime('last Sunday', $date));
		}
	}
	
	/**
	 * Vergelijkt twee Agendeerbaars op beginMoment t.b.v. sorteren.
	 */
	public static function vergelijkAgendeerbaars(Agendeerbaar $foo, Agendeerbaar $bar) {
		if ($foo->getBeginMoment() == $bar->getBeginMoment()) {
			return 0;
		}
		return ($foo->getBeginMoment() > $bar->getBeginMoment()) ? 1 : -1;
	}
}
?>
