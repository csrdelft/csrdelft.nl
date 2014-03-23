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
				'maand' => 'P_NOBODY',
				'icalendar' => 'P_NOBODY'
			);
		} else {
			$this->acl = array(
				'courant' => 'P_NOBODY',
				'toevoegen' => 'P_AGENDA_POST',
				'doorgaan' => 'P_AGENDA_POST',
				'bewerken' => 'P_AGENDA_MOD',
				'verwijderen' => 'P_AGENDA_MOD'
			);
		}
		$this->action = 'maand';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
			if ($this->action === 'calendar.ics') {
				$this->action = 'icalendar';
			}
		}
		$this->performAction($this->getParams(3));
	}

	public static function magToevoegen() {
		return LoginLid::instance()->hasPermission('P_AGENDA_POST');
	}

	public static function magBeheren() {
		return LoginLid::instance()->hasPermission('P_AGENDA_MOD');
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
		$this->view = new AgendaICalendarView($this->model);
	}

	public function courant() {
		require_once 'courant/courant.class.php';
		if (Courant::magBeheren()) {
			$this->view = new AgendaCourantView($this->model, 2);
		}
	}

	public function toevoegen($datum = '', $doorgaan = true) {
		$item = $this->model->newAgendaItem($datum);
		$this->view = new AgendaItemFormView($item, $this->action); // fetches POST values itself
		if ($doorgaan AND $this->view->validate()) {
			$id = $this->model->create($item);
			$item->item_id = (int) $id;
			setMelding('Toegevoegd: ' . $item->titel . ' (' . $item->begin_moment . ')', 1);
			$this->view = new AgendaItemMaandView($item);
			return true; // voor doorgaan
		}
	}

	public function doorgaan() {
		$this->action = 'toevoegen';
		if ($this->toevoegen()) {
			$item = $this->view->getModel();
			$_POST['datum_dag'] = date('d', $item->getEindMoment() + 60); // spring naar volgende dag bij 23:59
			$this->toevoegen('', false);
		}
	}

	public function bewerken($aid) {
		$item = $this->model->getAgendaItem($aid);
		$this->view = new AgendaItemFormView($item, $this->action); // fetches POST values itself
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
		if ($this->model->removeAgendaItem($aid)) {
			//setMelding('Verwijderd', 1);
			$this->view = new AgendaItemDeleteView($aid);
		}
	}

}
