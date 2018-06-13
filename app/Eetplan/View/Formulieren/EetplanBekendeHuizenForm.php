<?php

namespace App\Eetplan\View\Formulieren;

use App\Eetplan\Models\Eetplan;
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
    /**
     * @param Eetplan $model
     */
	public function __construct($model) {
		parent::__construct($model, '/eetplan/beheer/bekendehuizen/toevoegen', 'Noviet die een huis kent toevoegen', true);
		$fields[] = new RequiredLidField('uid', $model->uid, 'Noviet', 'novieten');
		$fields[] = new RequiredEntityField('woonoord', 'naam', 'Woonoord', WoonoordenModel::instance(), '/eetplan/beheer/bekendehuizen/zoeken?q=');

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
