<?php

namespace CsrDelft\view\aanmelder;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\aanmelder\AanmeldActiviteit;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\required\RequiredLidObjectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

class AanmeldActiviteitAanmeldForm implements FormulierTypeInterface {

	/**
	 * @param FormulierBuilder $builder
	 * @param AanmeldActiviteit $data
	 * @param array $options
	 */
	public function createFormulier(FormulierBuilder $builder, $data, $options = []) {
		$fields = [];
		$fields['lid'] = new RequiredLidObjectField('lid', null, 'Lid');
		$fields['aantal'] = new RequiredIntField('aantal', 1, 'Aantal personen', 1);
		$builder->addFields($fields);

		$builder->setFormKnoppen(new FormDefaultKnoppen(false, false));
	}
}
