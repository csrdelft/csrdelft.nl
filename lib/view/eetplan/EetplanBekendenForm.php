<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\entity\eetplan\EetplanBekenden;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredProfielEntityField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * Toevoegen voor EetplanBekendenTable op /eetplan/novietrelatie/toevoegen
 *
 * Class EetplanBekendenForm
 */
class EetplanBekendenForm extends ModalForm {
	function __construct(EetplanBekenden $model, $action, $update = false) {
		parent::__construct($model, $action, 'Novieten die elkaar kennen toevoegen', true);
		$fields[] = new HiddenField('id', $model->id);

		$fields['noviet1'] = new RequiredProfielEntityField('noviet1', $model->noviet1, 'Noviet 1', 'novieten');
		$fields['noviet2'] = new RequiredProfielEntityField('noviet2', $model->noviet2, 'Noviet 2', 'novieten');

		$fields[] = new TextareaField('opmerking', $model->opmerking, 'Opmerking');

		if ($update) {
			$fields['noviet1']->readonly = true;
			$fields['noviet2']->readonly = true;
		}

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();

		$this->modalBreedte = '';
	}
}
