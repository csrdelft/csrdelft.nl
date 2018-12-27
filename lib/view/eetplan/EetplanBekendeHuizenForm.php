<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\model\groepen\WoonoordenModel;
use CsrDelft\view\formulier\invoervelden\required\RequiredEntityField;
use CsrDelft\view\formulier\invoervelden\required\RequiredLidField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * Formulier voor noviet-huis relatie toevoegen op /eetplan/bekendehuizen/toevoegen
 *
 * Class EetplanBekendeHuizenForm
 */
class EetplanBekendeHuizenForm extends ModalForm {
	public function __construct($model) {
		parent::__construct($model, '/eetplan/bekendehuizen/toevoegen', 'Noviet die een huis kent toevoegen', true);
		$fields[] = new RequiredLidField('uid', $model->uid, 'Noviet', 'novieten');
		$fields[] = new RequiredEntityField('woonoord', 'naam', 'Woonoord', WoonoordenModel::instance(), '/eetplan/bekendehuizen/zoeken?q=');

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
