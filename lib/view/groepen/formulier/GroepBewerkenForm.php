<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\entity\groepen\Groep;
use CsrDelft\entity\groepen\GroepLid;
use CsrDelft\view\formulier\InlineForm;
use CsrDelft\view\formulier\invoervelden\SuggestieField;
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
			$field = new SuggestieField('opmerking', $lid->opmerking, null, $groep->getOpmerkingSuggesties());
			$field->placeholder = 'Opmerking';
		}

		parent::__construct($lid, $groep->getUrl() . '/ketzer/bewerken', $field, $toggle, $buttons);
	}

}
