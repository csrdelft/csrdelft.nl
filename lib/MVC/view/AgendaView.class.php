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
		$urlVorige = '/actueel/agenda/maand/';
		if ($this->maand == 1) {
			$urlVorige .= ($this->jaar - 1) . '-12/';
		} else {
			$urlVorige .= $this->jaar . '-' . ($this->maand - 1) . '/';
		}
		$this->smarty->assign('urlVorige', $urlVorige);

		// URL voor volgende maand
		$urlVolgende = '/actueel/agenda/maand/';
		if ($this->maand == 12) {
			$urlVolgende .= ($this->jaar + 1) . '-1/';
		} else {
			$urlVolgende .= $this->jaar . '-' . ($this->maand + 1) . '/';
		}
		$this->smarty->assign('urlVolgende', $urlVolgende);

		$this->smarty->display('MVC/agenda/maand.tpl');
	}

}

class AgendaItemFormView extends TemplateView {

	private $form;
	private $actie;

	public function __construct(AgendaItem $item, $actie) {
		parent::__construct($item);
		$this->actie = $actie;

		$fields[] = new TextField('titel', $item->titel, 'Titel');
		$fields[] = new DatumField('datum', $item->begin_moment, 'Datum');
		$fields['dag'] = new VinkField('heledag', $item->isHeledag(), 'Hele dag');
		$fields['dag']->onchange = 'toggleTijden(this.checked);';

		$fields[] = new HtmlComment('<div id="tijden" class="InputField"><label>Standaard tijden</label><div>
			<a onclick="setTijd(\'09\',\'00\',\'17\',\'30\');">» Dag</a> &nbsp;
			<a onclick="setTijd(\'18\',\'30\',\'22\',\'00\');">» Kring</a> &nbsp;
			<a onclick="setTijd(\'20\',\'00\',\'23\',\'59\');">» Avond</a> &nbsp;
			<a onclick="setTijd(\'20\',\'00\',\'22\',\'00\');">» Lezing</a> &nbsp;
		</div></div>');

		$fields[] = new TijdField('begin', $item->begin_moment, 'Van');
		$fields[] = new TijdField('eind', $item->eind_moment, 'Tot');
		$fields[] = new AutoresizeTextareaField('beschrijving', $item->beschrijving, 'Beschrijving');

		$fields[] = new SubmitButton($this->actie, '<a href="/actueel/agenda/maand/' . date('%Y-%m', $item->getBeginMoment()) . '" class="knop" style="float: none;">annuleren</a>');

		$this->form = new Formulier('agenda-item-form', null, $fields);
		$this->form->css_classes[] = 'agendaitem';
	}

	public function getTitel() {
		return 'Agenda - Item ' . $this->actie;
	}

	public function view() {
		$this->smarty->assign('form', $this->form);
		$this->smarty->assign('actie', $this->actie);
		$this->smarty->display('MVC/agenda/item_form.tpl');
	}

}

abstract class AgendaItemsView extends TemplateView {

	protected $items;

	public function __construct(AgendaModel $agenda, $aantalWeken) {
		parent::__construct($agenda);
		$beginMoment = strtotime(date('Y-m-d'));
		$eindMoment = strtotime('+' . $aantalWeken . ' weeks', $beginMoment);
		$eindMoment = strtotime('next saturday', $eindMoment);
		$this->items = $this->model->getAllAgendeerbaar($beginMoment, $eindMoment);
	}

}

class AgendaZijbalkView extends AgendaItemsView {

	public function getTitel() {
		return 'Agenda - Zijbalk';
	}

	public function view() {
		if (count($this->items) > LidInstellingen::get('zijbalk', 'agenda_max')) {
			$this->items = array_slice($this->items, 0, LidInstellingen::get('zijbalk', 'agenda_max'));
		}
		$this->smarty->assign('items', $this->items);
		$this->smarty->display('MVC/agenda/zijbalk.tpl');
	}

}

class AgendaCourantView extends AgendaItemsView {

	public function getTitel() {
		return 'Agenda - Courant';
	}

	public function view() {
		$this->smarty->assign('items', $this->items);
		$this->smarty->display('MVC/agenda/courant.tpl');
	}

}

class AgendaICalendarView extends TemplateView {

	public function __construct(AgendaModel $agenda) {
		parent::__construct($agenda);
	}

	public function getTitel() {
		return 'Agenda - iCalendar';
	}

	public function view() {
		$this->smarty->assign('items', $this->model->getiCalendarItems());
		$this->smarty->display('MVC/agenda/icalendar.tpl');
	}

}
