<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\eetplan\EetplanBekenden;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredProfielEntityField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

/**
 * Toevoegen voor EetplanBekendenTable op /eetplan/novietrelatie/toevoegen
 *
 * Class EetplanBekendenForm
 */
class EetplanBekendenForm implements FormulierTypeInterface
{
	/**
	 * @param FormulierBuilder $builder
	 * @param EetplanBekenden $data
	 * @param array $options
	 */
	public function createFormulier(FormulierBuilder $builder, $data, $options = []): void {
		$builder->setAction($options['action']);
		$builder->setDataTableId(true);
		$builder->setTitel('Novieten die elkaar kennen toevoegen');
		$fields[] = new HiddenField('id', $data->id);

		$fields['noviet1'] = new RequiredProfielEntityField(
			'noviet1',
			$data->noviet1,
			'Noviet 1',
			'novieten'
		);
		$fields['noviet2'] = new RequiredProfielEntityField(
			'noviet2',
			$data->noviet2,
			'Noviet 2',
			'novieten'
		);

		$fields[] = new TextareaField('opmerking', $data->opmerking, 'Opmerking');

		if ($options['update']) {
			$fields['noviet1']->readonly = true;
			$fields['noviet2']->readonly = true;
		}

		$builder->addFields($fields);

		$builder->setFormKnoppen(new FormDefaultKnoppen());
	}
}
