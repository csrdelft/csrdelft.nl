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
	 * Maandoverzicht laten zien. Als er een jaar-maand is meegegeven gebruiken
	 * we die, anders laten we de huidige maand zien.
	 */
	public function maand() {
		//Standaard tonen we het huidige jaar en maand.
		$jaar = date('Y');
		$maand = date('n');

		//is er een andere datum opgegeven? Dan gebruiken we die.
		$weergavedatum = '';
		if ($this->hasParam(0) AND $this->getParam(0) == 'maand') {
			$weergavedatum = $this->getParam(1);
		} elseif ($this->hasParam(0)) {
			$weergavedatum = $this->getParam(0);
		}

		if (preg_match('/^[0-9]{4}\-[0-9]{1,2}$/', $weergavedatum)) {
			$jaar = (int) substr($weergavedatum, 0, 4);
			$maand = (int) substr($weergavedatum, 5);
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
		$this->view = new AgendaICalendarContent($this->model);
	}

	/**
	 * Get courant agenda content
	 * 
	 * N.B. ajax-request, we doen zelf de $content->view() hier
	 */
	public function courant() {
		require_once 'courant/courant.class.php';
		if (Courant::magBeheren()) {
			$content = new AgendaCourantView($this->agenda, 2);
			$content->view();
		}
		exit;
	}

	/**
	 * Item toevoegen aan de agenda.
	 */
	public function toevoegen() {
		if ($this->isPosted()) {
			$item = $this->maakItem();
			if ($this->valideerItem($item)) {
				$this->model->saveAgendaItem($item);
				invokeRefresh('/actueel/agenda/maand/' . date('Y-m', $item->getBeginMoment()) . '/', 'Het agenda-item is succesvol toegevoegd.', 1);
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

			$item = new AgendaItem();
			$item->item_id = 0;
			$item->begin_moment = $beginMoment;
			$item->eind_moment = $eindMoment;
		}
		$this->view = new AgendaItemView($item, 'toevoegen');
		$this->view = new csrdelft($this->getContent());
		$this->view->addStylesheet('agenda.css');
	}

	public function bewerken() {
		if ($this->hasParam(1) && is_numeric($this->getParam(1))) {
			$itemId = (int) $this->getParam(1);
			if ($this->isPosted()) {
				$item = $this->maakItem($itemId);
				if ($this->valideerItem($item)) {
					$this->model->saveAgendaItem($item);
					invokeRefresh('/actueel/agenda/maand/' . date('Y-m', $item->getBeginMoment()) . '/', 'Het agenda-item is succesvol bewerkt.', 1);
				}
			} else {
				$item = $this->model->getMenuItem($itemId);
			}
		} else {
			invokeRefresh('/actueel/agenda/', 'Agenda-item niet gevonden.');
		}
		$this->view = new AgendaItemView($item, 'toevoegen');
		$this->view = new csrdelft($this->getContent());
		$this->view->addStylesheet('agenda.css');
	}

	public function verwijderen() {
		if ($this->hasParam(1) && is_numeric($this->getParam(1))) {
			$item = $this->model->getMenuItem((int) $this->getParam(1));
			$url = '/actueel/agenda/maand/' . date('Y-m', $item->getBeginMoment()) . '/';
			$this->model->delete($item);
			invokeRefresh($url, 'Het agenda-item is succesvol verwijderd.', 1);
		}
	}

	/**
	 * Maakt een nieuw AgendaItem met de gePOSTe gegevens.
	 */
	private function maakItem($itemId = 0) {
		$item = new AgendaItem();
		if (isset($_POST['heledag'])) {
			$beginMoment = strtotime($_POST['datum'] . ' 00:00');
			$eindMoment = strtotime($_POST['datum'] . ' 23:59');
		} else {
			$beginMoment = strtotime($_POST['datum'] . ' ' . $_POST['begin']);
			$eindMoment = strtotime($_POST['datum'] . ' ' . $_POST['eind']);
		}
		$item->item_id = $itemId;
		$item->begin_moment = getDateTime($beginMoment);
		$item->eind_moment = getDateTime($eindMoment);
		$item->titel = $_POST['titel'];
		$item->beschrijving = $_POST['beschrijving'];
		$item->rechten_bekijken = 'P_NOBODY';
		return $item;
	}

	/**
	 * Controleert of de ingevulde gegevens een geldig AgendaItem kunnen vormen.
	 * Geeft dat AgendaItem terug als dat het geval is, en false als dat niet 
	 * het geval is.
	 */
	private function valideerItem(AgendaItem $item) {
		if ($item->getTitel() == '') {
			setMelding('Titel mag niet leeg zijn');
			return false;
		}
		if ($item->getBeginMoment() >= $item->getEindMoment()) {
			$item->eind_moment = $item->begin_moment;
			setMelding('Beginmoment moet voor eindmoment liggen');
			return false;
		}
		if (date('Y-m-d', $item->getBeginMoment()) != date('Y-m-d', $item->getEindMoment())) {
			setMelding('Beginmoment en eindmoment moeten op dezelfde dag zijn');
			return false;
		}
		return true;
	}

}
