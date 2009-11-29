<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agenda.php
# -------------------------------------------------------------------
# Dataklassen voor de agenda.
# -------------------------------------------------------------------

require_once 'maaltijden/class.maaltrack.php';

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
	
	public function magToevoegen() {
		return LoginLid::instance()->hasPermission('P_AGENDA_POST');
	}
	
	public function magBeheren() {
		return LoginLid::instance()->hasPermission('P_AGENDA_MOD');
	}

	public function getItems($van=null, $tot=null, $filter=false) {
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
		
		// Maaltijden ophalen
		$maaltrack = new Maaltrack();		
		$result = array_merge($result, $maaltrack->getMaaltijden($van, $tot, true, true, null, false));
		
		// Sorteren
		usort($result, array('Agenda', 'vergelijkAgendeerbaars'));

		return $result;
	}

	public function getItemsByWeek($jaar=null, $week=null) {
		$van = null;
		$tot = null;

		return $this->getItems($van, $tot);
	}

	public function getItemsByMaand($jaar, $maand) {		
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
		// TODO: Huidige week mag hier wel een vinkje krijgen, kan ie een ander kleurtje krijgen.		
		$cur = $startMoment;		
		$agenda = array();
		while ($cur != $eindMoment) {
			$week = Agenda::weekNumber($cur);
			$dag = date('d', $cur);			
			$agenda[$week][$dag] = array();
			
			$cur = strtotime('+1 day', $cur);			
		}
				
		// Items toevoegen aan het array
		$items = $this->getItems($startMoment, $eindMoment);
		foreach ($items as $item) {
			$week = Agenda::weekNumber($item->getBeginMoment());
			$dag = date('d', $item->getEindMoment());
			$agenda[$week][$dag][] = $item;
		}	
		
		return $agenda;
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
		if ($foo->getBeginMoment() == $bar->getBeginMoment) {
			return 0;
		}
		return ($foo->getBeginMoment() > $bar->getBeginMoment()) ? 1 : -1;
	}
}
?>