<?php


# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agendacontroller.php
# -------------------------------------------------------------------
# Controller voor de agenda.
# -------------------------------------------------------------------

require_once 'agenda.class.php';
require_once 'agendacontent.class.php';
require_once 'MVC/controller/Controller.class.php';

class AgendaController extends Controller {

	private $agenda;

	public function __construct($queryString) {
		parent::__construct($queryString);

		// Agenda maken
		$this->agenda = new Agenda();

		// Actie opslaan
		$this->action = 'maand';
		if ($this->hasParam(0) AND $this->hasAction($this->getParam(0))) {
			$this->action = $this->getParam(0);
		}

		// Actie uitvoeren
		$this->performAction();
	}

	/**
	 * Weekoverzicht laten zien. Als er een jaar-week is meegegeven gebruiken
	 * we die, anders laten we de huidige week zien.
	 */
	public function week() {
		if ($this->hasParam(0) AND preg_match('/^[0-9]{4}\-[0-9]{1,2}$/', $this->getParam(0))) {
			$jaar = (int) substr($this->getParam(0), 0, 4);
			$week = (int) substr($this->getParam(0), 5);
		} else {
			$jaar = date('Y');
			$week = strftime('%U');
		}
		
		$this->content->setActie('week');
	}

	/**
	 * Maandoverzicht laten zien. Als er een jaar-maand is meegegeven gebruiken
	 * we die, anders laten we de huidige maand zien.
	 */
	public function maand() {
		//Standaard tonen we het huidige jaar en maand.
		$jaar=date('Y');
		$maand=date('n');
		
		//is er een andere datum opgegeven? Dan gebruiken we die.
		$weergavedatum='';
		if($this->hasParam(0) AND $this->getParam(0)=='maand'){
			$weergavedatum=$this->getParam(1);
		}elseif($this->hasParam(0)){
			$weergavedatum=$this->getParam(0);
		}
		
		if(preg_match('/^[0-9]{4}\-[0-9]{1,2}$/', $weergavedatum)){
			$jaar = (int) substr($weergavedatum, 0, 4);
			$maand = (int) substr($weergavedatum, 5);
		}
		
		$this->content = new AgendaMaandContent($this->agenda, $jaar, $maand);
	}

	/**
	 * Jaaroverzicht laten zien. Als er een jaar is meegegeven gebruiken we die,
	 * anders laten we dit jaar zien.
	 */
	public function jaar() {
		if ($this->hasParam(1) AND preg_match('/^[0-9]{4}$/', $this->getParam(1))) {
			$jaar = $this->getParam(1);
		} else {
			$jaar = date('Y');
		}
		
		$this->content->setActie('jaar');
	}
	
	/**
	 * iCalendar genereren.
	 */
	public function icalendar() {		
		$this->content = new AgendaIcalendarContent($this->agenda);
		$this->content->view();
		exit;
	}

	/**
	 * Item toevoegen aan de agenda.
	 */
	public function toevoegen() {
		if (!$this->agenda->magToevoegen()) {
			$this->action = 'geentoegang';
			$this->performAction();
			return;
		}
		
		if ($this->isPosted()) {
			$item = $this->maakItem();
			if ($this->valideerItem($item) === false) {
				
			} else {
				$item->opslaan();
				AgendaMaandContent::invokeRefresh('/actueel/agenda/'.date('Y-m', $item->getBeginMoment()).'/', 'Het agenda-item is succesvol toegevoegd.', 1);
			}
		} else {			
			if ($this->hasParam(1) AND preg_match('/^[0-9]{4}\-[0-9]{1,2}-[0-9]{1,2}$/', $this->getParam(1))) {
				$dag = strtotime($this->getParam(1));
			} else {
				$dag = time();
				// Afkappen naar 0:00
				$dag = strtotime(substr(date('Y-m-d', $dag), 0, 10)); 
			}
			
			$beginMoment = $dag + 72000;
			$eindMoment = $dag + 79200;
			
			$item = new AgendaItem(0, $beginMoment, $eindMoment);
		}
		
		$this->content = new AgendaItemContent($this->agenda, $item, 'toevoegen');
		$this->content->setMelding($this->errors);
	}
	
	public function bewerken() {
		if (!$this->agenda->magBeheren()) {
			$this->action = 'geentoegang';
			$this->performAction();
			return;
		}
		
		if ($this->hasParam(1) && is_numeric($this->getParam(1))) {
			$itemID = (int)$this->getParam(1);
			
			if ($this->isPosted()) {
				$item = $this->maakItem($itemID);
				if ($this->valideerItem($item) === false) {
					
				} else {
					$item->opslaan();
					AgendaMaandContent::invokeRefresh('/actueel/agenda/'.date('Y-m', $item->getBeginMoment()).'/', 'Het agenda-item is succesvol bewerkt.', 1);
				}
			} else {		
				$item = AgendaItem::getItem($itemID);
			}
		} else {
			AgendaMaandContent::invokeRefresh('/actueel/agenda/', 'Agenda-item niet gevonden.');
		}
		
		$this->content = new AgendaItemContent($this->agenda, $item, 'toevoegen');
		$this->content->setMelding($this->errors);
	}
		
	public function verwijderen() {
		if (!$this->agenda->magBeheren()) {
			$this->action = 'geentoegang';
			$this->performAction();
			return;
		}
		
		if ($this->hasParam(1) && is_numeric($this->getParam(1))) {
			$item = AgendaItem::getItem((int)$this->getParam(1));
			$url = '/actueel/agenda/'.date('Y-m', $item->getBeginMoment()).'/';
			if ($item->verwijder()) {
				AgendaMaandContent::invokeRefresh($url, 'Het agenda-item is succesvol verwijderd.', 1);
			} else {
				AgendaMaandContent::invokeRefresh($url, 'Het agenda-item kon niet worden verwijderd.');
			}
		}
	}
	function courant(){
		require_once 'courant/courant.class.php';
		if(Courant::magBeheren()){
			$content=new AgendaCourantContent($this->agenda, 2);
			
			$content->view();
		}
		//ajax-request, we doen zelf de $content->view() hier
		exit;
	}
	
	/**
	 * Maakt een nieuw AgendaItem met de gePOSTe gegevens.
	 */
	private function maakItem($itemId=0) {
		if(isset($_POST['heledag'])){
			$beginMoment = strtotime($_POST['datum'].' 00:00');
			$eindMoment = strtotime($_POST['datum'].' 23:59');
		}else{
			$beginMoment = strtotime($_POST['datum'].' '.$_POST['beginMoment']);
			$eindMoment = strtotime($_POST['datum'].' '.$_POST['eindMoment']);
		}
		return new AgendaItem($itemId, $beginMoment, $eindMoment, $_POST['titel'], $_POST['beschrijving'], 'P_NOBODY');
	}
	
	/**
	 * Controleert of de ingevulde gegevens een geldig AgendaItem kunnen vormen.
	 * Geeft dat AgendaItem terug als dat het geval is, en false als dat niet 
	 * het geval is.
	 */
	private function valideerItem($item) {
		if ($item->getTitel() == '') {
			$this->addError('Titel mag niet leeg zijn.');
		}
		if ($item->getBeginMoment() >= $item->getEindMoment()) {
			$this->addError('Beginmoment moet voor eindmoment liggen.');
			$item->setEindMoment($item->getBeginMoment());
		}
		if (date('Y-m-d', $item->getBeginMoment()) != date('Y-m-d', $item->getEindMoment())) {
			$this->addError('Beginmoment en eindmoment moeten op dezelfde dag zijn.');			
		}

		if ($this->valid) {
			return $item;
		} else {
			return false;
		}
	}
}
?>
