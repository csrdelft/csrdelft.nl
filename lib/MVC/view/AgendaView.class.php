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
	}

	public function getTitel() {
		return 'Agenda - Maandoverzicht voor ' . strftime('%B %Y', strtotime($this->jaar . '-' . $this->maand . '-01'));
	}

	public function view() {
		$cur = strtotime($this->jaar . '-' . $this->maand . '-01');
		$this->smarty->assign('datum', $cur);
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

	private $actie;

	public function __construct(AgendaItem $item, $actie) {
		parent::__construct($item);
		$this->actie = $actie;
	}

	public function view() {
		switch ($this->actie) {

			case 'toevoegen':
			case 'bewerken':
				$this->smarty->assign('magBeheren', AgendaController::magBeheren());
				$this->smarty->assign('item', $this->model);
				$this->smarty->display('MVC/agenda/maand_item.tpl');
				break;

			case 'verwijderen':
				echo '<div id="item-' . $this->model->item_id . '" class="remove"></div>';
				break;
		}
	}

}

class AgendaItemFormView extends Formulier {

	private $actie;

	public function __construct(AgendaItem $item, $actie, $param) {
		parent::__construct($item, 'agenda-item-form', '/agenda/' . $actie . '/' . $param);
		$this->actie = $actie;
		$this->css_classes[] = 'popup PreventUnchanged';

		$fields[] = new RequiredTextField('titel', $item->titel, 'Titel');
		$fields['datum'] = new DatumField('datum', $item->begin_moment, 'Datum', date('Y') + 5, date('Y') - 5);

		$html = '<div id="tijden" class="InputField"><label>Standaard tijden</label>';
		$tijden = explode(',', Instellingen::get('agenda', 'standaard_tijden'));
		$aantal = count($tijden) / 2;
		for ($i = 0; $i < $aantal; $i++) {
			$naam = $tijden[$i * 2 + 1];
			$tijd = explode('-', Instellingen::get('agenda', 'standaard_tijd_' . ($i + 1)));
			$begin = explode(':', $tijd[0]);
			$eind = explode(':', $tijd[1]);
			$html .= '<a onclick="setTijd(\'' . $begin[0] . '\',\'' . $begin[1] . '\',\'' . $eind[0] . '\',\'' . $eind[1] . '\');">» ' . $naam . '</a> &nbsp;';
		}
		$html .= '<div style="float:right;"><a title="Wijzig standaard tijden" href="/instellingenbeheer/module/agenda"><img width="16" height="16" class="icon" alt="edit" src="http://plaetjes.csrdelft.nl/famfamfam/pencil.png"></a></div>
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
		$fields[] = new SelectField('rechten_bekijken', $item->rechten_bekijken, 'Zichtbaar', array('P_LEDEN_READ' => 'Intern', 'P_NOBODY' => 'Extern'));
		$fields[] = new TextField('link', $item->link, 'Link');
		$fields[] = new AutoresizeTextareaField('beschrijving', $item->beschrijving, 'Beschrijving');
		$fields[] = new SubmitResetCancel();

		$this->addFields($fields);

		$this->model->begin_moment = $fields['datum']->getValue() . ' ' . $fields['begin']->getValue();
		$this->model->eind_moment = $fields['datum']->getValue() . ' ' . $fields['eind']->getValue();
	}

	public function view() {
		echo '<div id="popup-content"><h1>Agenda-item ' . $this->actie . '</h1>';
		echo parent::view();
		echo '</div>';
	}

	public function validate() {
		$fields = $this->getFields();
		if (strtotime($fields['eind']->getValue()) < strtotime($fields['begin']->getValue())) {
			$fields['eind']->error = 'Eindmoment moet na beginmoment liggen';
			return false;
		}
		return parent::validate();
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
		header("Content-Type: text/calendar");
		header('Content-Disposition: attachment; filename="calendar.ics"');

		$this->smarty->assign('CSR_ROOT', CSR_ROOT);
		$this->smarty->assign('items', $this->model->getiCalendarItems());
		$this->smarty->display('MVC/agenda/icalendar.tpl');
	}

}
