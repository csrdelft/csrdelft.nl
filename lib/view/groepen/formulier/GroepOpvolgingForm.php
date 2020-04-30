<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\groepen\GroepStatus;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\RadioField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class GroepOpvolgingForm extends ModalForm {

	public function __construct(
		AbstractGroep $groep,
		$action
	) {
		parent::__construct($groep, $action, 'Opvolging instellen', true);

		$fields = [];
		$fields['fam'] = new TextField('familie', $groep->familie, 'Familienaam');
		$fields['fam']->suggestions[] = $groep->getFamilieSuggesties();

		$options = array();
		foreach (GroepStatus::getTypeOptions() as $status) {
			$options[$status] = GroepStatus::getChar($status);
		}
		$fields[] = new RadioField('status', $groep->status, classNameZonderNamespace(Groepstatus::class), $options);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

}
