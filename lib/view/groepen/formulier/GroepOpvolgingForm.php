<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\enum\GroepStatus;
use CsrDelft\view\formulier\invoervelden\AutocompleteField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\EnumSelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class GroepOpvolgingForm extends ModalForm
{
	public function __construct(Groep $groep, $action)
	{
		parent::__construct($groep, $action, 'Opvolging instellen', true);

		$fields = [];
		$fields['fam'] = new AutocompleteField(
			'familie',
			$groep->familie,
			'Familienaam'
		);
		$fields['fam']->suggestions[] = $groep->getFamilieSuggesties();

		$fields[] = new EnumSelectField(
			'status',
			$groep->status,
			'Status',
			GroepStatus::class
		);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
