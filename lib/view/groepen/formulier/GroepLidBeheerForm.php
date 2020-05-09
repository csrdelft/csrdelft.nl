<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\entity\groepen\AbstractGroepLid;
use CsrDelft\view\formulier\FormFieldFactory;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class GroepLidBeheerForm extends ModalForm {

	public function __construct(
		AbstractGroepLid $lid,
		$action,
		array $blacklist = null
	) {
		parent::__construct($lid, $action, 'Aanmelding bewerken', true);
		$fields = FormFieldFactory::generateFields($this->model);

		if ($blacklist !== null) {
			$fields['uid']->blacklist = $blacklist;
			$fields['uid']->required = true;
			$fields['uid']->readonly = false;
		}
		$fields['uid']->hidden = false;
		$fields['door_uid']->required = true;
		$fields['door_uid']->readonly = true;
		$fields['door_uid']->hidden = true;

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

}
