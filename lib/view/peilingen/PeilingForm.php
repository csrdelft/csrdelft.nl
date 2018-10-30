<?php

namespace CsrDelft\view\peilingen;
use CsrDelft\model\entity\peilingen\Peiling;
use CsrDelft\view\formulier\getalvelden\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\RequiredTextareaField;
use CsrDelft\view\formulier\invoervelden\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\CheckboxField;
use CsrDelft\view\formulier\keuzevelden\RequiredCheckboxField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/10/2018
 */
class PeilingForm extends ModalForm
{
	/**
	 * PeilingForm constructor.
	 * @param Peiling $model
	 * @param $nieuw
	 * @throws \CsrDelft\common\CsrGebruikerException
	 */
	public function __construct($model, $nieuw)
	{
		$url = $nieuw ? '/peilingen/nieuw' : '/peilingen/bewerken';
		$titel = $nieuw ? 'Nieuwe peiling' : 'Peiling bewerken';
		parent::__construct($model, $url, $titel, true);

		$fields = [];

		$fields[] = new HiddenField('id', $model->id);
		$fields[] = new RequiredTextField('titel', $model->titel, 'Titel');
		$fields[] = new RequiredTextareaField('beschrijving', $model->beschrijving, 'Beschrijving');
		$fields[] = new CheckboxField('resultaat_zichtbaar', $model->resultaat_zichtbaar, 'Resultaat zichtbaar');
		$fields[] = new RequiredIntField('aantal_voorstellen', $model->aantal_voorstellen, 'Aantal voorstellen', 0, 10);
		$fields[] = new RequiredIntField('aantal_stemmen', $model->aantal_stemmen, 'Aantal stemmen', 0, 10);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

}
