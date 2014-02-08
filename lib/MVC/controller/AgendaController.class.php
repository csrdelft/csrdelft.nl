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
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'maand' => 'P_NOBODY',
				'icalendar' => 'P_NOBODY',
				'courant' => 'P_NOBODY',
				'toevoegen' => 'P_AGENDA_POST',
				'bewerken' => 'P_AGENDA_MOD',
				'verwijderen' => 'P_AGENDA_MOD'
			);
		} else {
			$this->acl = array(
				'toevoegen' => 'P_AGENDA_POST',
				'bewerken' => 'P_AGENDA_MOD'
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

	/**
	 * iCalendar genereren.
	 */
	public function icalendar() {
		$this->view = new AgendaICalendarView($this->model);
	}

	/**
	 * Get courant agenda content
	 * 
	 * N.B. ajax-request, we doen zelf de $content->view() hier
	 */
	public function courant() {
		require_once 'courant/courant.class.php';
		if (Courant::magBeheren()) {
			$content = new AgendaCourantView($this->model, 2);
			$content->view();
		}
		exit;
	}

	/**
	 * Item toevoegen aan de agenda.
	 */
	public function toevoegen($datum = '') {
		$item = new AgendaItem();
		$item->item_id = 0;
		if (!preg_match('/^[0-9]{4}\-[0-9]{1,2}-[0-9]{1,2}$/', $datum)) {
			$datum = strtotime('Y-m-d');
		}
		$item->begin_moment = getDateTime(strtotime($datum) + 72000);
		$item->eind_moment = getDateTime(strtotime($datum) + 79200);

		$this->view = new AgendaItemFormView($item, 'toevoegen'); // fetches POST values itself
		if ($this->view->validate()) {
			$this->model->saveAgendaItem($item->item_id, $this->view->getValues());
			setMelding('Toegevoegd', 1);
			$this->maand(date('Y-m', $item->getBeginMoment()));
		} else {
			$this->view = new csrdelft($this->getContent());
			$this->view->addStylesheet('agenda.css');
			$this->view->addScript('agenda.js');
		}
	}

	public function bewerken($aid) {
		$item = $this->model->getAgendaItem($aid);

		$this->view = new AgendaItemFormView($item, 'bewerken'); // fetches POST values itself
		if ($this->view->validate()) {
			$this->model->saveAgendaItem($item->item_id, $this->view->getValues());
			setMelding('Bijgewerkt', 1);
			$this->maand(date('Y-m', $item->getBeginMoment()));
		} else {
			$this->view = new csrdelft($this->getContent());
			$this->view->addStylesheet('agenda.css');
			$this->view->addScript('agenda.js');
		}
	}

	public function verwijderen($aid) {
		$item = $this->model->getAgendaItem((int) $aid);
		$this->model->delete($item);
		setMelding('Verwijderd', 1);
		$this->maand(date('Y-m', $item->getBeginMoment()));
	}

}
