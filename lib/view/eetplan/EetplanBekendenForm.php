<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\model\entity\eetplan\EetplanBekenden;
use CsrDelft\view\formulier\invoervelden\RequiredLidField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * Toevoegen voor EetplanBekendenTable op /eetplan/novietrelatie/toevoegen
 *
 * Class EetplanBekendenForm
 */
class EetplanBekendenForm extends ModalForm {
	function __construct(EetplanBekenden $model) {
		parent::__construct($model, '/eetplan/novietrelatie/toevoegen', false, true);
		$fields[] = new RequiredLidField('uid1', $model->uid1, 'Noviet 1', 'novieten');
		$fields[] = new RequiredLidField('uid2', $model->uid2, 'Noviet 2', 'novieten');
		$fields['btn'] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}
}
