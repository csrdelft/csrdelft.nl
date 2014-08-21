<?php

/**
 * AgendaView.class.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Klasse voor het weergeven begin agenda-gerelateerde dingen.
 */
class AgendaMaandView extends TemplateView {

	private $jaar;
	private $maand;

	public function __construct(AgendaModel $agenda, $jaar, $maand) {
		parent::__construct($agenda);
		$this->jaar = $jaar;
		$this->maand = $maand;
		$this->titel = 'Agenda - Maandoverzicht voor ' . strftime('%B %Y', strtotime($this->jaar . '-' . $this->maand . '-01'));
	}

	public function view() {
		$cur = strtotime($this->jaar . '-' . $this->maand . '-01');
		$this->smarty->assign('datum', $cur);
		$this->smarty->assign('weken', $this->model->getItemsByMaand($this->jaar, $this->maand));

		// URL voor vorige maand
		$urlVorige = '/agenda/maand/';
		if ($this->maand == 1) {
			$urlVorige .= ($this->jaar - 1) . '-12/';
		} else {
			$urlVorige .= $this->jaar . '-' . ($this->maand - 1) . '/';
		}
		$this->smarty->assign('urlVorige', $urlVorige);
		$this->smarty->assign('prevMaand', strftime('%B', strtotime('-1 Month', $cur)));

		// URL voor volgende maand
		$urlVolgende = '/agenda/maand/';
		if ($this->maand == 12) {
			$urlVolgende .= ($this->jaar + 1) . '-1/';
		} else {
			$urlVolgende .= $this->jaar . '-' . ($this->maand + 1) . '/';
		}
		$this->smarty->assign('urlVolgende', $urlVolgende);
		$this->smarty->assign('nextMaand', strftime('%B', strtotime('+1 Month', $cur)));

		$this->smarty->display('MVC/agenda/maand.tpl');
	}

}

class AgendaItemMaandView extends TemplateView {

	public function __construct(AgendaItem $item) {
		parent::__construct($item);
	}

	public function view() {
		$this->smarty->assign('item', $this->model);
		$this->smarty->display('MVC/agenda/maand_item.tpl');
	}

}

/**
 * Requires id of deleted agenda item.
 */
class AgendaItemDeleteView extends TemplateView {

	public function view() {
		echo '<div id="item-' . $this->model . '" class="remove"></div>';
	}

}

class AgendaItemForm extends PopupForm {

	public function __construct(AgendaItem $item, $actie) {
		parent::__construct($item, 'agenda-item-form', '/agenda/' . $actie . '/' . $item->item_id);
		$this->titel = 'Agenda-item ' . $actie;
		if ($actie === 'bewerken') {
			$this->css_classes[] = 'PreventUnchanged';
		}

		$fields['titel'] = new RequiredTextField('titel', $item->titel, 'Titel');
		$fields['titel']->suggestions = array('Kring', 'Lezing', 'Werkgroep', 'Eetplan', 'Borrel', 'Alpha-avond');
		$fields['datum'] = new DatumField('datum', date('Y-m-d', $item->getBeginMoment()), 'Datum', date('Y') + 5, date('Y') - 5);

		$html = '<div id="tijden" class="InputField" style="line-height: 2em;"><label>Standaard tijden</label>';
		$tijden = explode(',', Instellingen::get('agenda', 'standaard_tijden'));
		$aantal = count($tijden) / 2;
		for ($i = 0; $i < $aantal; $i++) {
			$naam = $tijden[$i * 2 + 1];
			$standaard_tijd = 'standaard_tijd_' . ($i + 1);
			if (!Instellingen::has('agenda', $standaard_tijd)) {
				setMelding($standaard_tijd . ' "' . $naam . '" is niet gedefinieerd', -1);
				continue;
			}
			$tijd = explode('-', Instellingen::get('agenda', $standaard_tijd));
			$begin = explode(':', $tijd[0]);
			$eind = explode(':', $tijd[1]);
			$html .= '<a onclick="setTijd(\'' . $begin[0] . '\',\'' . $begin[1] . '\',\'' . $eind[0] . '\',\'' . $eind[1] . '\');">» ' . $naam . '</a> &nbsp;';
		}
		$html .= '<div style="float:right;"><a title="Wijzig standaard tijden" href="/instellingenbeheer/module/agenda"><img width="16" height="16" class="icon" alt="edit" src="' . CSR_PICS . '/famfamfam/pencil.png"></a></div>
<script type="text/javascript">
function setTijd(a, b, c, d) {
	document.getElementById(\'field_begin_uur\').value = a;
	document.getElementById(\'field_begin_minuut\').value = b;
	document.getElementById(\'field_eind_uur\').value = c;
	document.getElementById(\'field_eind_minuut\').value = d;
}
</script>
</div>';
		$fields[] = new HtmlComment($html);

		$fields['begin'] = new TijdField('begin', date('H:i', $item->getBeginMoment()), 'Van');
		$fields['eind'] = new TijdField('eind', date('H:i', $item->getEindMoment()), 'Tot');

		$fields[] = new RechtenField('rechten_bekijken', $item->rechten_bekijken, 'Zichtbaar voor');

		$fields['l'] = new TextField('locatie', $item->locatie, 'Locatie');
		$fields['l']->title = 'Een kaart kan worden weergegeven in de agenda';

		$fields['u'] = new TextField('link', $item->link, 'Link');
		$fields['u']->title = 'URL als er op de titel geklikt wordt';

		$fields['b'] = new TextareaField('beschrijving', $item->beschrijving, 'Beschrijving');
		$fields['b']->title = 'Extra info als de cursor boven de titel gehouden wordt';

		$fields['btn'] = new FormButtons();
		if ($actie === 'toevoegen') {
			$fields['btn']->extraText = 'Opslaan en doorgaan';
			$fields['btn']->extraTitle = 'Opslaan & nog een agenda item toevoegen';
			$fields['btn']->extraIcon = 'add';
			$fields['btn']->extraUrl = '/agenda/toevoegen/doorgaan';
			$fields['btn']->extraAction = 'submit';
		}

		$this->addFields($fields);

		$this->model->begin_moment = $fields['datum']->getValue() . ' ' . $fields['begin']->getValue();
		$this->model->eind_moment = $fields['datum']->getValue() . ' ' . $fields['eind']->getValue();
	}

	public function validate() {
		$valid = parent::validate();
		$fields = $this->getFields();
		if (strtotime($fields['eind']->getValue()) < strtotime($fields['begin']->getValue())) {
			$fields['eind']->error = 'Eindmoment moet na beginmoment liggen';
			$valid = false;
		}
		return $valid;
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

	public function view() {
		if (count($this->items) > LidInstellingen::get('zijbalk', 'agenda_max')) {
			$this->items = array_slice($this->items, 0, LidInstellingen::get('zijbalk', 'agenda_max'));
		}
		$this->smarty->assign('items', $this->items);
		$this->smarty->display('MVC/agenda/zijbalk.tpl');
	}

}

class AgendaCourantView extends AgendaItemsView {

	public function view() {
		$this->smarty->assign('items', $this->items);
		$this->smarty->display('MVC/agenda/courant.tpl');
	}

}

class AgendaICalendarView extends TemplateView {

	public function __construct(AgendaModel $agenda) {
		parent::__construct($agenda);
	}

	public function view() {
		$this->smarty->assign('items', $this->model->getiCalendarItems());
		$this->smarty->display('MVC/agenda/icalendar.tpl');
	}

}
