<?php

require_once 'MVC/model/AgendaModel.class.php';
require_once 'MVC/view/AgendaView.class.php';

/**
 * AgendaController.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van de agenda.
 */
class AgendaController extends AclController {

	public function __construct($query) {
		parent::__construct($query, AgendaModel::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
				'maand'		 => 'P_AGENDA_READ',
				'icalendar'	 => 'P_AGENDA_READ'
			);
		} else {
			$this->acl = array(
				'courant'		 => 'P_MAIL_COMPOSE',
				'toevoegen'		 => 'P_AGENDA_ADD',
				'bewerken'		 => 'P_AGENDA_MOD',
				'verwijderen'	 => 'P_AGENDA_MOD'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'maand';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
			if ($this->action === 'calendar.ics') {
				$this->action = 'icalendar';
				header('Content-Disposition: attachment; filename="calendar.ics"');
			}
		}
		parent::performAction($this->getParams(3));
	}

	/**
	 * Maandoverzicht laten zien.
	 */
	public function maand($datum = '') {
		// Standaard tonen we het huidige jaar en maand, maar
		// als er een andere datum is opgegeven gebruiken we die.
		if (preg_match('/^[0-9]{4}\-[0-9]{1,2}$/', $datum)) {
			$jaar = (int) substr($datum, 0, 4);
			$maand = (int) substr($datum, 5);
		} else {
			$jaar = date('Y');
			$maand = date('n');
		}
		$body = new AgendaMaandView($this->model, $jaar, $maand);
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('agenda.css');
		$this->view->zijkolom = false;
	}

	public function icalendar() {
		header('Content-Type: text/calendar');
		$this->view = new AgendaICalendarView($this->model);
	}

	public function courant() {
		$this->view = new AgendaCourantView($this->model, 2);
	}

	public function toevoegen($datum = '') {
		$item = $this->model->newAgendaItem($datum);
		$this->view = new AgendaItemForm($item, $this->action); // fetches POST values itself
		if ($this->view->validate()) {
			$item->item_id = (int) $this->model->create($item);
			if ($datum === 'doorgaan') {
				setMelding('Toegevoegd: ' . $item->titel . ' (' . $item->begin_moment . ')', 1);
				$item->item_id = null;
				$_POST = array(); // clear post values of previous input
				$item->begin_moment = getDateTime($item->getEindMoment() + 60); // spring naar volgende dag bij 23:59
				$this->view = new AgendaItemForm($item, $this->action); // fetches POST values itself
			} else {
				$this->view = new AgendaItemMaandView($item);
			}
		}
	}

	public function bewerken($aid) {
		$item = $this->model->getAgendaItem($aid);
		$this->view = new AgendaItemForm($item, $this->action); // fetches POST values itself
		if ($this->view->validate()) {
			$rowcount = $this->model->update($item);
			if ($rowcount > 0) {
				//setMelding('Bijgewerkt', 1);
			} else {
				//setMelding('Geen wijzigingen', 0);
			}
			$this->view = new AgendaItemMaandView($item);
		}
	}

	public function verwijderen($aid) {
		$this->model->removeAgendaItem($aid);
		//setMelding('Verwijderd', 1);
		$this->view = new AgendaItemDeleteView($aid);
	}

}
