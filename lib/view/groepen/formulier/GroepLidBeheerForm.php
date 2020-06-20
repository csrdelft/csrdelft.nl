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

		unset($fields['uid']);
		unset($fields['groep_id']);

		if ($blacklist !== null) {
			$fields['profiel']->blacklist = $blacklist;
			$fields['profiel']->required = true;
			$fields['profiel']->readonly = false;
		}
		$fields['profiel']->hidden = false;
		$fields['profiel']->suggestieIdField = 'uid';
		$fields['door_uid']->required = true;
		$fields['door_uid']->readonly = true;
		$fields['door_uid']->hidden = true;

		$fields['profiel']->readonly = false;
		$fields['profiel']->suggestions = ['/tools/naamsuggesties?zoekin=alleleden&q='];

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

}
