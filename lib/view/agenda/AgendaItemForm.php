<?php

namespace CsrDelft\view\agenda;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\agenda\AgendaItem;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\invoervelden\required\RequiredRechtenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\DateTimeObjectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\knoppen\FormulierKnop;

class AgendaItemForm implements FormulierTypeInterface {
	/**
	 * @param FormulierBuilder $builder
	 * @param AgendaItem $data
	 * @param array $options
	 */
	public function createFormulier(FormulierBuilder $builder, $data, $options = []) {
		$builder->setAction('/agenda/' . $options['actie'] . ($data->item_id ? '/' . $data->item_id : ''));
		$builder->setTitel('Agenda-item ' . $options['actie']);
		if ($options['actie'] === 'bewerken') {
			$builder->addCssClass('PreventUnchanged');
		}

		$fields = [];
		$fields['titel'] = new RequiredTextField('titel', $data->titel, 'Titel');
		$fields['titel']->suggestions[] = array('Kring', 'Lezing', 'Werkgroep', 'Eetplan', 'Borrel', 'Alpha-avond');

		$fields['begin_moment'] = new DateTimeObjectField('begin_moment', $data->begin_moment, 'Begin moment');
		$fields['begin_moment']->required = true;
		$fields['eind_moment'] = new DateTimeObjectField('eind_moment', $data->eind_moment, 'Eind moment');
		$fields['eind_moment']->required = true;

		$fields['eind_moment']->from_datetime = $fields['begin_moment'];
		$fields['begin_moment']->to_datetime = $fields['eind_moment'];


		$fields['r'] = new RequiredRechtenField('rechten_bekijken', $data->rechten_bekijken, 'Zichtbaar voor');
		$fields['r']->readonly = !LoginService::mag(P_AGENDA_MOD);


		$fields['l'] = new TextField('locatie', $data->locatie, 'Locatie');
		$fields['l']->title = 'Een kaart kan worden weergegeven in de agenda';

		$fields['u'] = new TextField('link', $data->link, 'Link');
		$fields['u']->title = 'URL als er op de titel geklikt wordt';

		$fields['b'] = new TextareaField('beschrijving', $data->beschrijving, 'Beschrijving');
		$fields['b']->title = 'Extra info als de cursor boven de titel gehouden wordt';

		$builder->addFields($fields);

		$formKnoppen = new FormDefaultKnoppen();
		if ($options['actie'] === 'toevoegen') {
			$doorgaan = new FormulierKnop('/agenda/toevoegen/doorgaan', 'submit', 'Opslaan en doorgaan', 'Opslaan & nog een agenda item toevoegen', 'add');
			$formKnoppen->addKnop($doorgaan, false, true);
		}

		$builder->setFormKnoppen($formKnoppen);

		$builder->addValidationMethod(function($fields) {
			if ($fields['eind_moment']->getValue() !== null && $fields['eind_moment']->getValue() < $fields['begin_moment']->getValue()) {
				$fields['eind_moment']->error = 'Eindmoment moet na beginmoment liggen';

				return false;
			}

			return true;
		});
	}
}
