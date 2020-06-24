<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\entity\eetplan\Eetplan;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\view\formulier\invoervelden\required\RequiredDoctrineEntityField;
use CsrDelft\view\formulier\invoervelden\required\RequiredLidField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * Formulier voor noviet-huis relatie toevoegen op /eetplan/bekendehuizen/toevoegen
 *
 * Class EetplanBekendeHuizenForm
 */
class EetplanBekendeHuizenForm extends ModalForm {
	/**
	 * EetplanBekendeHuizenForm constructor.
	 * @param Eetplan $model
	 * @param bool $update
	 */
	public function __construct($model, $action, $update = false) {
		parent::__construct($model, $action, 'Noviet die een huis kent toevoegen', true);
		$fields['uid'] = new RequiredLidField('uid', $model->uid, 'Noviet', 'novieten');
		$fields['woonoord'] = new RequiredDoctrineEntityField('woonoord', $model->woonoord, 'Woonoord', Woonoord::class, '/eetplan/bekendehuizen/zoeken?q=');
		$fields[] = new TextareaField('opmerking', $model->opmerking, 'Opmerking');

		if ($update) {
			$fields['uid']->readonly = true;
			$fields['woonoord']->readonly = true;
		}

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
