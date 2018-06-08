<?php

namespace App\Eetplan\View\Formulieren;

use App\Eetplan\Models\EetplanBekenden;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\invoervelden\RequiredLidField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * Toevoegen voor EetplanBekendenTable op /eetplan/novietrelatie/toevoegen
 */
class EetplanBekendenForm extends ModalForm {
    /**
     * @param EetplanBekenden $model
     * @throws CsrGebruikerException
     */
	function __construct($model) {
		parent::__construct($model, '/eetplan/beheer/bekenden/toevoegen', 'Novieten die elkaar kennen toevoegen', true);
		$fields['id'] = new IntField('id', $model->id, '');
		$fields['id']->hidden = true;
		$fields[] = new RequiredLidField('uid1', $model->uid1, 'Noviet 1', 'novieten');
		$fields[] = new RequiredLidField('uid2', $model->uid2, 'Noviet 2', 'novieten');

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();

		$this->modalBreedte = '';
	}
}
