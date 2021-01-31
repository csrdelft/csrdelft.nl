<?php

namespace CsrDelft\view\civimelder;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\civimelder\Reeks;
use CsrDelft\view\formulier\elementen\Subkopje;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\BBCodeField;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredRechtenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredJaNeeField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

class ReeksForm implements FormulierTypeInterface {

	/**
	 * @param FormulierBuilder $builder
	 * @param Reeks $data
	 * @param array $options
	 */
	public function createFormulier(FormulierBuilder $builder, $data, $options = []) {
		//$url = $nieuw ? '/peilingen/nieuw' : '/peilingen/bewerken';

		$builder->setTitel($options['nieuw'] ? 'Nieuwe reeks' : 'Reeks bewerken');
		$builder->setDataTableId(true);

		$fields = [];

		$fields[] = new HiddenField('id', $data->getId());
		$fields[] = new RequiredTextField('naam', $data->getNaam(), 'Reeks naam');
		$fields[] = new RequiredRechtenField('rechtenAanmaken', $data->getRechtenAanmaken(), 'Rechten activiteiten aanmaken');

		$fields[] = new Subkopje('Standaardgegevens activiteit');
		$fields[] = new RequiredTextField('titel', $data->getRawTitel(), 'Titel');
		$fields[] = new BBCodeField('beschrijving', $data->getRawBeschrijving(), 'Beschrijving');
		$fields[] = new RequiredIntField('capaciteit', $data->getRawCapaciteit(), 'Capaciteit', 0);
		$fields[] = new RequiredRechtenField('rechtenAanmelden', $data->getRawRechtenAanmelden(), 'Rechten aanmelden');
		$fields[] = new RequiredRechtenField('rechtenLijstBekijken', $data->getRawRechtenLijstBekijken(), 'Rechten lijst bekijken');
		$fields[] = new RequiredRechtenField('rechtenLijstBeheren', $data->getRawRechtenLijstBeheren(), 'Rechten lijst beheren');
		$fields[] = new RequiredIntField('maxGasten', $data->getRawMaxGasten(), 'Max. gasten per persoon', 0);
		$fields[] = new RequiredJaNeeField('aanmeldenMogelijk', $data->isRawAanmeldenMogelijk(), 'Aanmelden mogelijk');
		$fields[] = new IntField('aanmeldenVanaf', $data->getRawAanmeldenVanaf(), 'Aanmelden vanaf (minuten van tevoren)');
		$fields[] = new IntField('aanmeldenTot', $data->getRawAanmeldenTot(), 'Aanmelden tot (minuten van tevoren)');
		$fields[] = new RequiredJaNeeField('afmeldenMogelijk', $data->isRawAfmeldenMogelijk(), 'Afmelden mogelijk');
		$fields[] = new IntField('afmeldenTot', $data->getRawAfmeldenTot(), 'Afmelden tot (minuten van tevoren)');

//		$fields[] = new HiddenObjectField('eigenaarProfiel', $data->eigenaarProfiel, Profiel::class);
//		$fields[] = new RequiredTextField('titel', $data->titel, 'Titel');
//		$fields[] = new RequiredBBCodeField('beschrijving', $data->beschrijving, 'Beschrijving');
//		$fields[] = new JaNeeField('resultaat_zichtbaar', $data->resultaat_zichtbaar, 'Resultaat zichtbaar');
//		$fields[] = new RequiredIntField('aantal_voorstellen', $data->aantal_voorstellen ?? 0, 'Aantal voorstellen', 0, 10);
//		$fields[] = new RequiredIntField('aantal_stemmen', $data->aantal_stemmen ?? 1, 'Aantal stemmen', 0, 10);
//		$fields[] = new DateTimeObjectField('sluitingsdatum', $data->sluitingsdatum, 'Sluitingsdatum');
//		$fields[] = new RechtenField('rechten_stemmen', $data->rechten_stemmen, 'Rechten stemmen');
//		$fields['rechten_mod'] = new RechtenField('rechten_mod', $data->rechten_mod, 'Rechten bewerken');
//		$fields['rechten_mod']->title = 'Een peiling mag altijd bewerkt worden door jou, de BASFCie, de PubCie en het bestuur.';


		$builder->addFields($fields);

		$builder->setFormKnoppen(new FormDefaultKnoppen());
	}
}
