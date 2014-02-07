<?php

# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.agendacontent.php
# -------------------------------------------------------------------
# Klasse voor het weergeven van agenda-gerelateerde dingen.
# -------------------------------------------------------------------

require_once 'agenda.class.php';

class AgendaMaandContent extends TemplateView {

	private $agenda;
	private $jaar;
	private $maand;

	public function __construct($agenda, $jaar, $maand) {
		parent::__construct();
		$this->agenda = $agenda;
		$this->jaar = $jaar;
		$this->maand = $maand;
	}

	public function getTitel() {
		return 'Agenda - Maandoverzicht voor ' . strftime('%B %Y', strtotime($this->jaar . '-' . $this->maand . '-01'));
	}

	public function view() {
		$filter = !LoginLid::instance()->hasPermission('P_AGENDA_MOD');

		$this->smarty->assign('datum', strtotime($this->jaar . '-' . $this->maand . '-01'));
		$this->smarty->assign('weken', $this->agenda->getItemsByMaand($this->jaar, $this->maand, $filter));
		$this->smarty->assign('magToevoegen', $this->agenda->magToevoegen());
		$this->smarty->assign('magBeheren', $this->agenda->magBeheren());

		// URL voor vorige maand
		$urlVorige = CSR_ROOT . 'actueel/agenda/';
		if ($this->maand == 1) {
			$urlVorige .= ($this->jaar - 1) . '-12/';
		} else {
			$urlVorige .= $this->jaar . '-' . ($this->maand - 1) . '/';
		}
		$this->smarty->assign('urlVorige', $urlVorige);

		// URL voor volgende maand
		$urlVolgende = CSR_ROOT . 'actueel/agenda/';
		if ($this->maand == 12) {
			$urlVolgende .= ($this->jaar + 1) . '-1/';
		} else {
			$urlVolgende .= $this->jaar . '-' . ($this->maand + 1) . '/';
		}
		$this->smarty->assign('urlVolgende', $urlVolgende);

		$this->smarty->display('agenda/maand.tpl');
	}

}

class AgendaItemContent extends TemplateView {

	private $agenda;
	private $item;
	private $actie;

	public function __construct($agenda, $item, $actie) {
		parent::__construct();
		$this->agenda = $agenda;
		$this->item = $item;
		$this->actie = $actie;
	}

	public function getTitel() {
		return 'Agenda - Item toevoegen';
	}

	public function view() {
		$this->smarty->assign('item', $this->item);
		$this->smarty->assign('actie', $this->actie);
		$this->smarty->display('agenda/item.tpl');
	}

}

class AgendaZijbalkContent extends TemplateView {

	private $agenda;
	private $aantalWeken;

	public function __construct($agenda, $aantalWeken) {
		parent::__construct();
		$this->agenda = $agenda;
		$this->aantalWeken = $aantalWeken;
	}

	public function getTitel() {
		return 'Agenda - Zijbalk';
	}

	public function view() {
		$filter = !LoginLid::instance()->hasPermission('P_AGENDA_MOD');

		$beginMoment = strtotime(date('Y-m-d'));
		$eindMoment = strtotime('+' . $this->aantalWeken . ' weeks', $beginMoment);
		$eindMoment = strtotime('next saturday', $eindMoment);
		$items = $this->agenda->getItems($beginMoment, $eindMoment, $filter);

		if (count($items) > LidInstellingen::get('zijbalk', 'agenda_max')) {
			$items = array_slice($items, 0, LidInstellingen::get('zijbalk', 'agenda_max'));
		}

		$this->smarty->assign('items', $items);
		$this->smarty->display('agenda/zijbalk.tpl');
	}

}

class AgendaCourantContent extends TemplateView {

	private $agenda;
	private $aantalWeken;

	public function __construct($agenda, $aantalWeken) {
		parent::__construct();
		$this->agenda = $agenda;
		$this->aantalWeken = $aantalWeken;
	}

	public function view() {
		$filter = !LoginLid::instance()->hasPermission('P_AGENDA_MOD');

		$beginMoment = strtotime(date('Y-m-d'));
		$eindMoment = strtotime('+' . $this->aantalWeken . ' weeks', $beginMoment);
		$eindMoment = strtotime('next saturday', $eindMoment);
		$items = $this->agenda->getItems($beginMoment, $eindMoment, $filter);


		$this->smarty->assign('items', $items);
		$this->smarty->display('agenda/courant.tpl');
	}

}

class AgendaIcalendarContent extends TemplateView {

	private $agenda;

	public function __construct($agenda) {
		parent::__construct();
		$this->agenda = $agenda;
	}

	public function view() {
		$filter = !LoginLid::instance()->hasPermission('P_AGENDA_MOD');
		$items = $this->agenda->getItems(null, null, $filter);


		$this->smarty->assign('items', $items);
		$this->smarty->display('agenda/icalendar.tpl');
	}

}

?>
