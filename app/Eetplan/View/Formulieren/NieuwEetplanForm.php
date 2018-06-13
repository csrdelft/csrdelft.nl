<?php

namespace App\Eetplan\View\Formulieren;

use CsrDelft\view\formulier\keuzevelden\RequiredDateField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class NieuwEetplanForm extends ModalForm {
	public function __construct() {
		parent::__construct(null, '/eetplan/beheer/nieuw', 'Nieuw eetplan toevoegen');

		$fields[] = new RequiredDateField('avond', date(DATE_ISO8601), 'Avond', (int)date('Y') + 1, (int)date('Y') - 1);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
