<?php

/**
 * AgendaView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Klasse voor het weergeven begin agenda-gerelateerde dingen.
 */
abstract class AgendaView extends SmartyTemplateView {

	public function getBreadcrumbs() {
		return '<a href="/agenda" title="Agenda"><span class="fa fa-calendar module-icon"></span></a>';
	}

}

class AgendaMaandView extends AgendaView {

	private $jaar;
	private $maand;

	public function __construct(AgendaModel $agenda, $jaar, $maand) {
		parent::__construct($agenda);
		$this->jaar = $jaar;
		$this->maand = $maand;
		$this->titel = 'Maandoverzicht voor ' . strftime('%B %Y', strtotime($this->jaar . '-' . $this->maand . '-01'));
	}

	public function getTitel() {
		return 'Agenda - ' . $this->titel;
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » ' . $this->getDropDownYear() . ' » ' . $this->getDropDownMonth();
	}

	private function getDropDownYear() {
		$dropdown = '<select onchange="location.href=this.value;">';
		$minyear = $this->jaar - 5;
		$maxyear = $this->jaar + 5;
		for ($year = $minyear; $year <= $maxyear; $year++) {
			$dropdown .= '<option value="/agenda/maand/' . $year . '/' . $this->maand . '"';
			if ($year == $this->jaar) {
				$dropdown .= ' selected="selected"';
			}
			$dropdown .= '>' . $year . '</option>';
		}
		$dropdown .= '</select>';
		return $dropdown;
	}

	private function getDropDownMonth() {
		$dropdown = '<select onchange="location.href=this.value;">';
		for ($month = 1; $month <= 12; $month++) {
			$dropdown .= '<option value="/agenda/maand/' . $this->jaar . '/' . $month . '"';
			if ($month == $this->maand) {
				$dropdown .= ' selected="selected"';
			}
			$dropdown .= '>' . strftime('%B', strtotime($this->jaar . '-' . $month . '-01')) . '</option>';
		}
		$dropdown .= '</select>';
		return $dropdown;
	}

	public function view() {
		$cur = strtotime($this->jaar . '-' . $this->maand . '-01');
		$this->smarty->assign('datum', $cur);
		$this->smarty->assign('weken', $this->model->getItemsByMaand($this->jaar, $this->maand));

		// URL voor vorige maand
		$urlVorige = '/agenda/maand/';
		if ($this->maand == 1) {
			$urlVorige .= ($this->jaar - 1) . '/12';
		} else {
			$urlVorige .= $this->jaar . '/' . ($this->maand - 1);
		}
		$this->smarty->assign('urlVorige', $urlVorige);
		$this->smarty->assign('prevMaand', strftime('%B', strtotime('-1 Month', $cur)));

		// URL voor volgende maand
		$urlVolgende = '/agenda/maand/';
		if ($this->maand == 12) {
			$urlVolgende .= ($this->jaar + 1) . '/1';
		} else {
			$urlVolgende .= $this->jaar . '/' . ($this->maand + 1);
		}
		$this->smarty->assign('urlVolgende', $urlVolgende);
		$this->smarty->assign('nextMaand', strftime('%B', strtotime('+1 Month', $cur)));

		$this->smarty->display('agenda/maand.tpl');
	}

}

class AgendaItemMaandView extends AgendaView {

	public function __construct(AgendaItem $item) {
		parent::__construct($item);
	}

	public function view() {
		$this->smarty->assign('item', $this->model);
		$this->smarty->display('agenda/maand_item.tpl');
	}

}

/**
 * Requires id of deleted agenda item.
 */
class AgendaItemDeleteView extends AgendaView {

	public function view() {
		echo '<div id="item-' . $this->model . '" class="remove"></div>';
	}

}

class AgendaItemForm extends ModalForm {

	public function __construct(AgendaItem $item, $actie) {
		parent::__construct($item, '/agenda/' . $actie . '/' . $item->item_id);
		$this->titel = 'Agenda-item ' . $actie;
		if ($actie === 'bewerken') {
			$this->css_classes[] = 'PreventUnchanged';
		}

		$fields['titel'] = new RequiredTextField('titel', $item->titel, 'Titel');
		$fields['titel']->suggestions[] = array('Kring', 'Lezing', 'Werkgroep', 'Eetplan', 'Borrel', 'Alpha-avond');

		$fields['begin_moment'] = new RequiredDateTimeField('begin_moment', $item->begin_moment, 'Begin moment');
		$fields['eind_moment'] = new RequiredDateTimeField('eind_moment', $item->eind_moment, 'Eind moment');

		$fields['eind_moment']->from_datetime = $fields['begin_moment'];
		$fields['begin_moment']->to_datetime = $fields['eind_moment'];

		$fields['r'] = new RequiredRechtenField('rechten_bekijken', $item->rechten_bekijken, 'Zichtbaar voor');
		$fields['r']->readonly = !LoginModel::mag('P_AGENDA_MOD');

		$fields['l'] = new TextField('locatie', $item->locatie, 'Locatie');
		$fields['l']->title = 'Een kaart kan worden weergegeven in de agenda';

		$fields['u'] = new TextField('link', $item->link, 'Link');
		$fields['u']->title = 'URL als er op de titel geklikt wordt';

		$fields['b'] = new TextareaField('beschrijving', $item->beschrijving, 'Beschrijving');
		$fields['b']->title = 'Extra info als de cursor boven de titel gehouden wordt';

		$fields['btn'] = new FormDefaultKnoppen();
		if ($actie === 'toevoegen') {
			$doorgaan = new FormulierKnop('/agenda/toevoegen/doorgaan', 'submit', 'Opslaan en doorgaan', 'Opslaan & nog een agenda item toevoegen', '/famfamfam/add.png');
			$fields['btn']->addKnop($doorgaan, false, true);
		}

		$this->addFields($fields);
	}

	public function validate() {
		$fields = $this->getFields();
		if ($fields['eind_moment']->getValue() !== null AND strtotime($fields['eind_moment']->getValue()) < strtotime($fields['begin_moment']->getValue())) {
			$fields['eind_moment']->error = 'Eindmoment moet na beginmoment liggen';
		}
		return parent::validate();
	}

}

abstract class AgendaItemsView extends AgendaView {

	protected $items;

	public function __construct(AgendaModel $agenda, $aantalWeken) {
		parent::__construct($agenda);
		$beginMoment = strtotime(date('Y-m-d'));
		$eindMoment = strtotime('+' . $aantalWeken . ' weeks', $beginMoment);
		$eindMoment = strtotime('next saturday', $eindMoment);
		$this->items = $this->model->getAllAgendeerbaar($beginMoment, $eindMoment, false, $this instanceof AgendaZijbalkView);
	}

}

class AgendaZijbalkView extends AgendaItemsView {

	public function view() {
		if (count($this->items) > LidInstellingen::get('zijbalk', 'agenda_max')) {
			$this->items = array_slice($this->items, 0, LidInstellingen::get('zijbalk', 'agenda_max'));
		}
		$this->smarty->assign('items', $this->items);
		$this->smarty->display('agenda/zijbalk.tpl');
	}

}

class AgendaCourantView extends AgendaItemsView {

	public function view() {
		$this->smarty->assign('items', $this->items);
		$this->smarty->display('agenda/courant.tpl');
	}

}

class AgendaICalendarView extends AgendaView {

	public function __construct(AgendaModel $agenda) {
		parent::__construct($agenda);
	}

	public function view() {
		$this->smarty->assign('items', $this->model->getICalendarItems());
		$this->smarty->assign('published', str_replace('-', '', str_replace(':', '', str_replace('+00:00', 'Z', gmdate('c')))));
		$this->smarty->display('agenda/icalendar.tpl');
	}

}
