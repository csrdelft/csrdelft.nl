<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\entity\groepen\enum\GroepVersie;
use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\view\formulier\FormFieldFactory;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class GroepLidBeheerForm extends ModalForm {

	public function __construct(
		GroepLid $lid,
		$action
	) {
		parent::__construct($lid, $action, $lid->uid ? 'Aanmelding bewerken' : 'Aanmelding toevoegen', true);
		$fields = FormFieldFactory::generateFields($this->model);

		if (GroepVersie::isV1($lid->groep->versie)) {
			unset($fields['opmerking2']);
		}

		if (GroepVersie::isV2($lid->groep->versie)) {
			unset($fields['opmerking']);
		}

		unset($fields['uid']);
		unset($fields['groepId']);
		unset($fields['groep']);

		$fields['profiel']->required = true;
		$fields['profiel']->hidden = false;
		$fields['doorUid']->required = true;
		$fields['doorUid']->readonly = true;
		$fields['doorUid']->hidden = true;

		$fields['profiel']->readonly = false;
		$fields['profiel']->zoekin = 'alleleden';

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

}
