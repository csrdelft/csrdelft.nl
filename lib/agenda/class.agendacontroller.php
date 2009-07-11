<?php


# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agendacontroller.php
# -------------------------------------------------------------------
# Controller voor de agenda.
# -------------------------------------------------------------------

require_once ('class.agenda.php');
require_once ('class.agendacontent.php');
require_once ('class.controller.php');

class AgendaController extends Controller {

	private $agenda;

	public function __construct($queryString) {
		parent::__construct($queryString);

		// Agenda maken
		$this->agenda = new Agenda();

		// Content-object aanmaken
		$this->content = new AgendaContent($this->agenda);

		// Actie opslaan
		if ($this->hasParam(0) AND $this->hasAction($this->getParam(0))) {
			$this->action = $this->getParam(0);
		}

		// Actie uitvoeren
		$this->performAction();
	}

	public function action_default() {
		$this->action_week();
	}

	/**
	 * Weekoverzicht laten zien. Als er een jaar-week is meegegeven gebruiken
	 * we die, anders laten we de huidige week zien.
	 */
	public function action_week() {
		if ($this->hasParam(1) AND preg_match('/^[0-9]{4}\-[0-9]{1,2}$/', $this->getParam(1))) {
			$jaar = (int) substr($this->getParam(1), 0, 4);
			$week = (int) substr($this->getParam(1), 5);
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
	public function action_maand() {
		if ($this->hasParam(1) AND preg_match('/^[0-9]{4}\-[0-9]{1,2}$/', $this->getParam(1))) {
			$jaar = (int) substr($this->getParam(1), 0, 4);
			$maand = (int) substr($this->getParam(1), 5);
		} else {
			$jaar = date('Y');
			$maand = date('n');
		}
		
		$this->content->setActie('maand');
	}

	/**
	 * Jaaroverzicht laten zien. Als er een jaar is meegegeven gebruiken we die,
	 * anders laten we dit jaar zien.
	 */
	public function action_jaar() {
		if ($this->hasParam(1) AND preg_match('/^[0-9]{4}$/', $this->getParam(1))) {
			$jaar = $this->getParam(1);
		} else {
			$jaar = date('Y');
		}
		
		$this->content->setActie('jaar');
	}

	public function action_toevoegen() {

	}
	
	public function action_bewerken() {

	}

	public function action_verwijderen() {

	}
}
?>