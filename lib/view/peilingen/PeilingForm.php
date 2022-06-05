<?php

namespace CsrDelft\view\peilingen;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\peilingen\Peiling;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\HiddenObjectField;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredProsemirrorField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\DateTimeObjectField;
use CsrDelft\view\formulier\keuzevelden\JaNeeField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/10/2018
 */
class PeilingForm implements FormulierTypeInterface
{
	/**
	 * @param FormulierBuilder $builder
	 * @param Peiling $data
	 * @param array $options
	 */
	public function createFormulier(
		FormulierBuilder $builder,
		$data,
		$options = []
	) {
		//$url = $nieuw ? '/peilingen/nieuw' : '/peilingen/bewerken';

		$builder->setTitel(
			$options['nieuw'] ? 'Nieuwe peiling' : 'Peiling bewerken'
		);
		$builder->setDataTableId($options['dataTableId']);

		$fields = [];

		$fields[] = new HiddenField('id', $data->id);
		$fields[] = new HiddenObjectField(
			'eigenaarProfiel',
			$data->eigenaarProfiel,
			Profiel::class
		);
		$fields[] = new RequiredTextField('titel', $data->titel, 'Titel');
		$fields[] = new RequiredProsemirrorField(
			'beschrijving',
			$data->beschrijving,
			'Beschrijving'
		);
		$fields[] = new JaNeeField(
			'resultaat_zichtbaar',
			$data->resultaat_zichtbaar,
			'Resultaat zichtbaar'
		);
		$fields[] = new RequiredIntField(
			'aantal_voorstellen',
			$data->aantal_voorstellen ?? 0,
			'Aantal voorstellen',
			0,
			10
		);
		$fields[] = new RequiredIntField(
			'aantal_stemmen',
			$data->aantal_stemmen ?? 1,
			'Aantal stemmen',
			0,
			10
		);
		$fields[] = new DateTimeObjectField(
			'sluitingsdatum',
			$data->sluitingsdatum,
			'Sluitingsdatum'
		);
		$fields[] = new RechtenField(
			'rechten_stemmen',
			$data->rechten_stemmen,
			'Rechten stemmen'
		);
		$fields['rechten_mod'] = new RechtenField(
			'rechten_mod',
			$data->rechten_mod,
			'Rechten bewerken'
		);
		$fields['rechten_mod']->title =
			'Een peiling mag altijd bewerkt worden door jou, de BASFCie, de PubCie en het bestuur.';

		$builder->addFields($fields);

		$builder->setFormKnoppen(new FormDefaultKnoppen());
	}
}
