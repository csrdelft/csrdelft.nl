<?php

namespace CsrDelft\view\eetplan;


use CsrDelft\entity\eetplan\Eetplan;
use CsrDelft\view\formulier\elementen\HtmlBbComment;
use CsrDelft\view\formulier\keuzevelden\required\RequiredSelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class VerwijderEetplanForm extends ModalForm {
	/**
	 * @param Eetplan[] $avonden
	 */
	public function __construct($avonden) {
		parent::__construct(null, '/eetplan/verwijderen', 'Eetplan verwijderen');

		$fields = [];
		$fields[] = new HtmlBbComment('[b]Let op, verwijderen van een eetplan kan niet ongedaan gemaakt worden.[/b]');

		$avondenLijst = [];
		foreach ($avonden as $eetplan) {
			$avondenLijst[$eetplan->avond] = $eetplan->avond;
		}

		$fields[] = new RequiredSelectField('avond', null, 'Avond', $avondenLijst);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
