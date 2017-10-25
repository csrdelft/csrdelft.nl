<?php

namespace CsrDelft\view\eetplan;


use CsrDelft\model\entity\eetplan\Eetplan;
use CsrDelft\view\formulier\elementen\HtmlBbComment;
use CsrDelft\view\formulier\keuzevelden\RequiredSelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class VerwijderEetplanForm extends ModalForm {
	/**
	 * @param Eetplan[] $avonden
	 */
	public function __construct($avonden) {
		parent::__construct(null, '/eetplan/verwijderen', 'Eetplan verwijderen');

		$fields[] = new HtmlBbComment('[b]Let op, verwijderen van een eetplan kan niet ongedaan gemaakt worden.[/b]');

		$avondenLijst = [];
		foreach ($avonden as $eetplan) {
			$avondenLijst[$eetplan->avond] = $eetplan->avond;
		}

		$fields[] = new RequiredSelectField('avond', null, 'Avond', $avondenLijst);
		$fields['btn'] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}
}
