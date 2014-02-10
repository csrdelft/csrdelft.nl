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
		$urlVorige = '/agenda/maand/';
		if ($this->maand == 1) {
			$urlVorige .= ($this->jaar - 1) . '-12/';
		} else {
			$urlVorige .= $this->jaar . '-' . ($this->maand - 1) . '/';
		}
		$this->smarty->assign('urlVorige', $urlVorige);

		// URL voor volgende maand
		$urlVolgende = '/agenda/maand/';
		if ($this->maand == 12) {
			$urlVolgende .= ($this->jaar + 1) . '-1/';
		} else {
			$urlVolgende .= $this->jaar . '-' . ($this->maand + 1) . '/';
		}
		$this->smarty->assign('urlVolgende', $urlVolgende);

		$this->smarty->display('MVC/agenda/maand.tpl');
	}

}

class AgendaItemMaandView extends TemplateView {

	public function __construct(AgendaItem $item) {
		parent::__construct($item);
	}

	public function getTitel() {
		return 'Agenda - Item';
	}

	public function view() {
		$this->smarty->assign('item', $this->model);
		$this->smarty->display('MVC/agenda/maand_item.tpl');
	}

}

class AgendaItemFormView extends TemplateView implements Validator {

	private $form;
	private $actie;

	public function __construct(AgendaItem $item, $actie) {
		parent::__construct($item);
		$this->actie = $actie;

		$fields[] = new RequiredTextField('titel', $item->titel, 'Titel');
		$fields[] = new DatumField('datum', $item->begin_moment, 'Datum', date('Y') + 5, date('Y') - 5);
		$fields[] = new HtmlComment('<div id="tijden" class="InputField"><label>Standaard tijden</label><div>
			<a onclick="setTijd(\'00\',\'00\',\'23\',\'59\');">» Hele dag</a> &nbsp;
			<a onclick="setTijd(\'18\',\'30\',\'22\',\'30\');">» Kring</a> &nbsp;
			<a onclick="setTijd(\'20\',\'00\',\'22\',\'00\');">» Lezing</a> &nbsp;
			<a onclick="setTijd(\'20\',\'00\',\'23\',\'59\');">» Borrel</a> &nbsp;
		</div></div>');
		$fields['van'] = new TijdField('begin', date('H:i', $item->getBeginMoment()), 'Van');
		$fields['tot'] = new TijdField('eind', date('H:i', $item->getEindMoment()), 'Tot');
		$fields[] = new SelectField('rechten', $item->rechten_bekijken, 'Zichtbaar', array('P_LEDEN_READ' => 'Intern', 'P_NOBODY' => 'Extern'));
		$fields[] = new AutoresizeTextareaField('beschrijving', $item->beschrijving, 'Beschrijving');

		$fields[] = new SubmitResetCancel('/agenda/maand/' . date('Y-m', $item->getBeginMoment()));

		$this->form = new Formulier('agenda-item-form', 'agenda/opslaan/' . $item->item_id, $fields);
		$this->form->css_classes[] = 'popup CheckChanged';
	}

	public function getTitel() {
		return 'Agenda - Item ' . $this->actie;
	}

	public function view() {
		$this->smarty->assign('form', $this->form);
		$this->smarty->assign('actie', $this->actie);
		$this->smarty->display('MVC/agenda/item_form.tpl');
	}

	public function validate() {
		$fields = $this->form->getFields();
		if (strtotime($fields['tot']->getValue()) < strtotime($fields['van']->getValue())) {
			$fields['tot']->error = 'Eindmoment moet na beginmoment liggen';
			return false;
		}
		return $this->form->validate();
	}

	public function getError() {
		return $this->form->error;
	}

	public function getModel() {
		$properties = $this->form->getValues();
		$this->model->titel = $properties['titel'];
		$this->model->begin_moment = $properties['datum'] . ' ' . $properties['begin'];
		$this->model->eind_moment = $properties['datum'] . ' ' . $properties['eind'];
		$this->model->beschrijving = $properties['beschrijving'];
		$this->model->rechten_bekijken = $properties['rechten'];
		return $this->model;
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
