<?php

namespace CsrDelft\view\aanmelder;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\aanmelder\Reeks;
use CsrDelft\view\formulier\elementen\Subkopje;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\ProsemirrorField;
use CsrDelft\view\formulier\invoervelden\required\RequiredRechtenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredJaNeeField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

class ReeksForm implements FormulierTypeInterface
{
	/**
	 * @param FormulierBuilder $builder
	 * @param Reeks $data
	 * @param array $options
	 */
	public function createFormulier(
		FormulierBuilder $builder,
		$data,
		$options = []
	) {
		$builder->setTitel($options['nieuw'] ? 'Nieuwe reeks' : 'Reeks bewerken');
		$builder->setDataTableId(true);

		$fields = [];

		$fields[] = new HiddenField('id', $data->getId());
		$fields[] = new RequiredTextField('naam', $data->getNaam(), 'Reeks naam');
		$fields[] = new RequiredRechtenField(
			'rechtenAanmaken',
			$data->getRechtenAanmaken(),
			'Rechten activiteiten aanmaken'
		);

		$fields[] = new Subkopje('Standaardgegevens activiteit');
		$fields[] = new RequiredTextField('titel', $data->getRawTitel(), 'Titel');
		$fields[] = new ProsemirrorField(
			'beschrijving',
			$data->getRawBeschrijving(),
			'Beschrijving'
		);
		$fields[] = new RequiredIntField(
			'capaciteit',
			$data->getRawCapaciteit(),
			'Capaciteit',
			0
		);
		$fields[] = new RequiredRechtenField(
			'rechtenAanmelden',
			$data->getRawRechtenAanmelden(),
			'Rechten aanmelden'
		);
		$fields[] = new RequiredRechtenField(
			'rechtenLijstBekijken',
			$data->getRawRechtenLijstBekijken(),
			'Rechten lijst bekijken'
		);
		$fields[] = new RequiredRechtenField(
			'rechtenLijstBeheren',
			$data->getRawRechtenLijstBeheren(),
			'Rechten lijst beheren'
		);
		$fields[] = new RequiredIntField(
			'maxGasten',
			$data->getRawMaxGasten(),
			'Max. gasten per persoon',
			0
		);
		$fields[] = new RequiredJaNeeField(
			'aanmeldenMogelijk',
			$data->isRawAanmeldenMogelijk(),
			'Aanmelden mogelijk'
		);
		$fields[] = new IntField(
			'aanmeldenVanaf',
			$data->getRawAanmeldenVanaf(),
			'Aanmelden vanaf (minuten voor einde)'
		);
		$fields[] = new IntField(
			'aanmeldenTot',
			$data->getRawAanmeldenTot(),
			'Aanmelden tot (minuten voor einde)'
		);
		$fields[] = new RequiredJaNeeField(
			'afmeldenMogelijk',
			$data->isRawAfmeldenMogelijk(),
			'Afmelden mogelijk'
		);
		$fields[] = new IntField(
			'afmeldenTot',
			$data->getRawAfmeldenTot(),
			'Afmelden tot (minuten voor einde)'
		);
		$builder->addFields($fields);

		$builder->setFormKnoppen(new FormDefaultKnoppen());
	}
}
