<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\entity\groepen\AbstractGroep;
use CsrDelft\entity\groepen\AbstractGroepLid;
use CsrDelft\view\formulier\InlineForm;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\MultiSelectField;

class GroepBewerkenForm extends InlineForm {

	public function __construct(
		AbstractGroepLid $lid,
		AbstractGroep $groep,
		$toggle = true,
		$buttons = true
	) {

		if ($groep->keuzelijst) {
			$field = new MultiSelectField('opmerking', $lid->opmerking, null, $groep->keuzelijst);
		} else {
			$field = new TextField('opmerking', $lid->opmerking, null);
			$field->placeholder = 'Opmerking';
			$field->suggestions[] = $groep->getOpmerkingSuggesties();
		}

		parent::__construct($lid, $groep->getUrl() . '/ketzer/bewerken', $field, $toggle, $buttons);
	}

}
