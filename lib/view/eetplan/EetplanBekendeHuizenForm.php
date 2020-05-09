<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\eetplan\Eetplan;
use CsrDelft\repository\groepen\WoonoordenRepository;
use CsrDelft\view\formulier\invoervelden\required\RequiredEntityField;
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
		$woonoord = $model->getWoonoord() ? $model->getWoonoord() : null;
		$fields['woonoord'] = new RequiredEntityField('woonoord', 'naam', 'Woonoord', ContainerFacade::getContainer()->get(WoonoordenRepository::class), '/eetplan/bekendehuizen/zoeken?q=', $woonoord);
		$fields[] = new TextareaField('opmerking', $model->opmerking, 'Opmerking');

		if ($update) {
			$fields['uid']->readonly = true;
			$fields['woonoord']->readonly = true;
		}

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
