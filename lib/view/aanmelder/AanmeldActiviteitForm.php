<?php

namespace CsrDelft\view\aanmelder;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\entity\aanmelder\AanmeldActiviteit;
use CsrDelft\view\formulier\elementen\CollapsableSubkopje;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredDateTimeObjectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

class AanmeldActiviteitForm implements FormulierTypeInterface
{
	/**
	 * @param FormulierBuilder $builder
	 * @param AanmeldActiviteit $data
	 * @param array $options
	 */
	public function createFormulier(FormulierBuilder $builder, $data, $options = []): void {
		$builder->setTitel(
			$options['nieuw'] ? 'Nieuwe activiteit' : 'Activiteit bewerken'
		);
		$builder->setDataTableId(true);

		$fields = [];

		$fields[] = new HiddenField('id', $data->getId());
		$fields[] = new RequiredDateTimeObjectField(
			'start',
			$data->getStart(),
			'Start'
		);
		$fields[] = new RequiredDateTimeObjectField(
			'einde',
			$data->getEinde(),
			'Einde'
		);

		$fields[] = new CollapsableSubkopje('Gegevens activiteit', true);
		$fields[] = new HtmlComment('Laat leeg voor standaard van reeks');
		$fields['titel'] = new TextField('titel', $data->getRawTitel(), 'Titel');
		$fields['titel']->placeholder = $data->getReeks()->getRawTitel();
		$fields['capaciteit'] = new IntField(
			'capaciteit',
			$data->getRawCapaciteit(),
			'Capaciteit',
			0
		);
		$fields['capaciteit']->placeholder = $data->getReeks()->getRawCapaciteit();
		$fields['rechtenAanmelden'] = new RechtenField(
			'rechtenAanmelden',
			$data->getRawRechtenAanmelden(),
			'Rechten aanmelden'
		);
		$fields['rechtenAanmelden']->placeholder = $data
			->getReeks()
			->getRawRechtenAanmelden();
		$fields['rechtenLijstBekijken'] = new RechtenField(
			'rechtenLijstBekijken',
			$data->getRawRechtenLijstBekijken(),
			'Rechten lijst bekijken'
		);
		$fields['rechtenLijstBekijken']->placeholder = $data
			->getReeks()
			->getRawRechtenLijstBekijken();
		$fields['rechtenLijstBeheren'] = new RechtenField(
			'rechtenLijstBeheren',
			$data->getRawRechtenLijstBeheren(),
			'Rechten lijst beheren'
		);
		$fields['rechtenLijstBeheren']->placeholder = $data
			->getReeks()
			->getRawRechtenLijstBeheren();
		$fields['maxGasten'] = new IntField(
			'maxGasten',
			$data->getRawMaxGasten(),
			'Max. gasten per persoon',
			0
		);
		$fields['maxGasten']->placeholder = $data->getReeks()->getRawMaxGasten();
		$fields['aanmeldenVanaf'] = new IntField(
			'aanmeldenVanaf',
			$data->getRawAanmeldenVanaf(),
			'Aanmelden vanaf (minuten voor einde)'
		);
		$fields['aanmeldenVanaf']->placeholder = $data
			->getReeks()
			->getRawAanmeldenVanaf();
		$fields['aanmeldenTot'] = new IntField(
			'aanmeldenTot',
			$data->getRawAanmeldenTot(),
			'Aanmelden tot (minuten voor einde)'
		);
		$fields['aanmeldenTot']->placeholder = $data
			->getReeks()
			->getRawAanmeldenTot();
		$fields['afmeldenTot'] = new IntField(
			'afmeldenTot',
			$data->getRawAfmeldenTot(),
			'Afmelden tot (minuten voor einde)'
		);
		$fields['afmeldenTot']->placeholder = $data
			->getReeks()
			->getRawAfmeldenTot();
		$fields[] = new HtmlComment('</div>');
		$builder->addFields($fields);

		$builder->setFormKnoppen(new FormDefaultKnoppen());
	}
}
