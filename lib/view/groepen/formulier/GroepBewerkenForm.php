<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\view\formulier\InlineForm;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\MultiSelectField;

class GroepBewerkenForm extends InlineForm
{

	public function __construct(
		GroepLid $lid,
		Groep $groep,
		$toggle = true,
		$buttons = true
	)
	{

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
