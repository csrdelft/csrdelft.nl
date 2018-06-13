<?php

namespace App\Eetplan\View\Formulieren;


use CsrDelft\view\formulier\elementen\HtmlBbComment;
use CsrDelft\view\formulier\keuzevelden\RequiredSelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class VerwijderEetplanForm extends ModalForm {
	/**
	 * @param string[] $avonden
	 */
	public function __construct($avonden) {
		parent::__construct(null, '/eetplan/beheer/verwijderen', 'Eetplan verwijderen');

		$fields[] = new HtmlBbComment('[b]Let op, verwijderen van een eetplan kan niet ongedaan gemaakt worden.[/b]');

		$fields[] = new RequiredSelectField('avond', null, 'Avond', $avonden);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
