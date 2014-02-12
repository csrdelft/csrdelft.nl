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

	/**
	 * Data access model
	 * @var AgendaModel
	 */
	private $model;

	public function __construct($query) {
		parent::__construct($query);
		$this->model = new AgendaModel();
		if (!$this->isPosted()) {
			$this->acl = array(
				'maand' => 'P_NOBODY',
				'icalendar' => 'P_NOBODY',
				'toevoegen' => 'P_AGENDA_POST',
				'bewerken' => 'P_AGENDA_MOD'
			);
		} else {
			$this->acl = array(
				'courant' => 'P_NOBODY',
				'toevoegen' => 'P_AGENDA_POST',
				'bewerken' => 'P_AGENDA_MOD',
				'verwijderen' => 'P_AGENDA_MOD'
			);
		}
		$this->action = 'maand';
		if ($this->hasParam(0)) {
			$this->action = $this->getParam(0);
		}
		$this->performAction($this->getParams(1));
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
		$this->view = new AgendaMaandView($this->model, $jaar, $maand);
		$this->view = new csrdelft($this->getContent());
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

	public function toevoegen($datum = '') {
		$item = $this->model->newAgendaItem($datum);
		$this->view = new AgendaItemFormView($item, 'toevoegen'); // fetches POST values itself
		if ($this->isPosted() AND $this->view->validate()) {
			$id = $this->model->create($item);
			$item->item_id = $id;
			//setMelding('Toegevoegd', 1);
			$this->view = new AgendaItemMaandView($item, 'toevoegen');
		}
	}

	public function bewerken($aid) {
		$item = $this->model->getAgendaItem($aid);
		$this->view = new AgendaItemFormView($item, 'bewerken'); // fetches POST values itself
		if ($this->isPosted() AND $this->view->validate()) {
			$rowcount = $this->model->update($item);
			if ($rowcount > 0) {
				//setMelding('Bijgewerkt', 1);
			} else {
				//setMelding('Geen wijzigingen', 0);
			}
			$this->view = new AgendaItemMaandView($item, 'bewerken');
		}
	}

	public function verwijderen($aid) {
		$item = $this->model->getAgendaItem($aid);
		$this->model->delete($item);
		//setMelding('Verwijderd', 1);
		$this->view = new AgendaItemMaandView($item, 'verwijderen');
	}

}
