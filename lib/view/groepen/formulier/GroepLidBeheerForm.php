<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\model\entity\groepen\AbstractGroepLid;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class GroepLidBeheerForm extends ModalForm {

	public function __construct(
		AbstractGroepLid $lid,
		$action,
		array $blacklist = null
	) {
		parent::__construct($lid, $action, 'Aanmelding bewerken', true);
		$fields = $this->generateFields();

		if ($blacklist !== null) {
			$fields['uid']->blacklist = $blacklist;
			$fields['uid']->required = true;
			$fields['uid']->readonly = false;
		}
		$fields['uid']->hidden = false;
		$fields['door_uid']->required = true;
		$fields['door_uid']->readonly = true;
		$fields['door_uid']->hidden = true;

		$fields[] = new FormDefaultKnoppen();
		$this->addFields($fields);
	}

}
