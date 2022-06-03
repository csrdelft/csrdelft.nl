<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\eetplan\Eetplan;
use CsrDelft\entity\groepen\Woonoord;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredDoctrineEntityField;
use CsrDelft\view\formulier\invoervelden\required\RequiredProfielEntityField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

/**
 * Formulier voor noviet-huis relatie toevoegen op /eetplan/bekendehuizen/toevoegen
 *
 * Class EetplanBekendeHuizenForm
 */
class EetplanBekendeHuizenForm implements FormulierTypeInterface {
	/**
	 * @param FormulierBuilder $builder
	 * @param Eetplan $data
	 * @param array $options
	 */
	public function createFormulier(FormulierBuilder $builder, $data, $options = []) {
		$builder->setDataTableId(true);
		$builder->setTitel('Noviet die een huis kent toevoegen');
		$builder->setAction($options['action']);
		$fields[] = new HiddenField('id', $data->id);
		$fields['noviet'] = new RequiredProfielEntityField('noviet', $data->noviet, 'Noviet', 'novieten');
		$fields['woonoord'] = new RequiredDoctrineEntityField('woonoord', $data->woonoord, 'Woonoord', Woonoord::class, '/eetplan/bekendehuizen/zoeken?q=');
		$fields[] = new TextareaField('opmerking', $data->opmerking, 'Opmerking');

		if ($options['update']) {
			$fields['noviet']->readonly = true;
			$fields['woonoord']->readonly = true;
		}

		$builder->addFields($fields);

		$builder->setFormKnoppen(new FormDefaultKnoppen());
	}
}
