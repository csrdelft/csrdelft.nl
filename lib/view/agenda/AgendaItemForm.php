<?php

namespace CsrDelft\view\agenda;

use CsrDelft\model\entity\agenda\AgendaItem;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\invoervelden\RequiredRechtenField;
use CsrDelft\view\formulier\invoervelden\RequiredTextField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\RequiredDateTimeField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\knoppen\FormulierKnop;
use CsrDelft\view\formulier\ModalForm;

class AgendaItemForm extends ModalForm {

	public function __construct(
		AgendaItem $item,
		$actie
	) {
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
			$doorgaan = new FormulierKnop('/agenda/toevoegen/doorgaan', 'submit', 'Opslaan en doorgaan', 'Opslaan & nog een agenda item toevoegen', 'add');
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
