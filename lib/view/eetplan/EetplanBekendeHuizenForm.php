<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\view\formulier\invoervelden\RequiredEntityField;
use CsrDelft\view\formulier\invoervelden\RequiredLidField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * Formulier voor noviet-huis relatie tovoegen op /eetplan/bekendehuizen/toevoegen
 *
 * Class EetplanBekendeHuizenForm
 */
class EetplanBekendeHuizenForm extends ModalForm {
	public function __construct($model) {
		parent::__construct($model, '/eetplan/bekendehuizen/toevoegen', false, true);
		$fields[] = new RequiredLidField('uid', $model->uid, 'Noviet', 'novieten');
		$fields[] = new RequiredEntityField('woonoord', 'naam', 'Woonoord', WoonoordenModel::instance(), '/eetplan/bekendehuizen/zoeken?q=');

		$fields['btn'] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}
}
