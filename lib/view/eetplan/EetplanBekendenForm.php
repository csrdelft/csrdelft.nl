<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\entity\eetplan\EetplanBekenden;
use CsrDelft\view\formulier\invoervelden\required\RequiredLidField;
use CsrDelft\view\formulier\invoervelden\required\RequiredLidObjectField;
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
		$fields['uid1'] = new RequiredLidObjectField('noviet1', $model->noviet1, 'Noviet 1', 'novieten');
		$fields['uid2'] = new RequiredLidObjectField('noviet2', $model->noviet2, 'Noviet 2', 'novieten');
		$fields[] = new TextareaField('opmerking', $model->opmerking, 'Opmerking');

		if ($update) {
			$fields['uid1']->readonly = true;
			$fields['uid2']->readonly = true;
		}

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();

		$this->modalBreedte = '';
	}
}
