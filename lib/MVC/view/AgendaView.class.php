<?php

require_once 'MVC/controller/AgendaController.class.php';

/**
 * AgendaView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Klasse voor het weergeven van agenda-gerelateerde dingen.
 */
class AgendaMaandView extends TemplateView {

	private $jaar;
	private $maand;

	public function __construct(AgendaModel $agenda, $jaar, $maand) {
		parent::__construct($agenda);
		$this->jaar = $jaar;
		$this->maand = $maand;
	}

	public function getTitel() {
		return 'Agenda - Maandoverzicht voor ' . strftime('%B %Y', strtotime($this->jaar . '-' . $this->maand . '-01'));
	}

	public function view() {
		$this->smarty->assign('datum', strtotime($this->jaar . '-' . $this->maand . '-01'));
		$this->smarty->assign('weken', $this->model->getItemsByMaand($this->jaar, $this->maand));
		$this->smarty->assign('magToevoegen', AgendaController::magToevoegen());
		$this->smarty->assign('magBeheren', AgendaController::magBeheren());

		// URL voor vorige maand
		$urlVorige = CSR_ROOT . 'actueel/agenda/maand/';
		if ($this->maand == 1) {
			$urlVorige .= ($this->jaar - 1) . '-12/';
		} else {
			$urlVorige .= $this->jaar . '-' . ($this->maand - 1) . '/';
		}
		$this->smarty->assign('urlVorige', $urlVorige);

		// URL voor volgende maand
		$urlVolgende = CSR_ROOT . 'actueel/agenda/maand/';
		if ($this->maand == 12) {
			$urlVolgende .= ($this->jaar + 1) . '-1/';
		} else {
			$urlVolgende .= $this->jaar . '-' . ($this->maand + 1) . '/';
		}
		$this->smarty->assign('urlVolgende', $urlVolgende);

		$this->smarty->display('agenda/maand.tpl');
	}

}

class AgendaItemView extends TemplateView {

	private $actie;

	public function __construct(AgendaItem $item, $actie) {
		parent::__construct($item);
		$this->actie = $actie;
	}

	public function getTitel() {
		return 'Agenda - Item ' . $this->actie;
	}

	public function view() {
		$this->smarty->assign('item', $this->model);
		$this->smarty->assign('actie', $this->actie);
		$this->smarty->display('agenda/item_form.tpl');
	}

}

class AgendaZijbalkView extends TemplateView {

	private $aantalWeken;

	public function __construct($agenda, $aantalWeken) {
		parent::__construct($agenda);
		$this->aantalWeken = $aantalWeken;
	}

	public function getTitel() {
		return 'Agenda - Zijbalk';
	}

	public function view() {
		$beginMoment = strtotime(date('Y-m-d'));
		$eindMoment = strtotime('+' . $this->aantalWeken . ' weeks', $beginMoment);
		$eindMoment = strtotime('next saturday', $eindMoment);
		$items = $this->model->getAllAgendeerbaar($beginMoment, $eindMoment);

		if (count($items) > LidInstellingen::get('zijbalk', 'agenda_max')) {
			$items = array_slice($items, 0, LidInstellingen::get('zijbalk', 'agenda_max'));
		}

		$this->smarty->assign('items', $items);
		$this->smarty->display('agenda/zijbalk.tpl');
	}

}

class AgendaCourantView extends TemplateView {

	private $aantalWeken;

	public function __construct(AgendaModel $agenda, $aantalWeken) {
		parent::__construct($agenda);
		$this->aantalWeken = $aantalWeken;
	}

	public function getTitel() {
		return 'Agenda - Courant';
	}

	public function view() {
		$beginMoment = strtotime(date('Y-m-d'));
		$eindMoment = strtotime('+' . $this->aantalWeken . ' weeks', $beginMoment);
		$eindMoment = strtotime('next saturday', $eindMoment);
		$items = $this->model->getAllAgendeerbaar($beginMoment, $eindMoment);

		$this->smarty->assign('items', $items);
		$this->smarty->display('agenda/courant.tpl');
	}

}

class AgendaICalendarContent extends TemplateView {

	public function __construct(AgendaModel $agenda) {
		parent::__construct($agenda);
	}

	public function getTitel() {
		return 'Agenda - iCalendar';
	}

	public function view() {
		$this->smarty->assign('items', $this->model->getiCalendarItems());
		$this->smarty->display('agenda/icalendar.tpl');
	}

}
