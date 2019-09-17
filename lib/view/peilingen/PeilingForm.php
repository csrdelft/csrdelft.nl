<?php

namespace CsrDelft\view\peilingen;

use CsrDelft\model\entity\peilingen\Peiling;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\RechtenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredBBCodeField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\DateTimeField;
use CsrDelft\view\formulier\keuzevelden\JaNeeField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/10/2018
 */
class PeilingForm extends ModalForm
{
	/**
	 * @param Peiling $model
	 * @param boolean $nieuw
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
		$fields[] = new RequiredBBCodeField('beschrijving', $model->beschrijving, 'Beschrijving');
		$fields[] = new JaNeeField('resultaat_zichtbaar', $model->resultaat_zichtbaar, 'Resultaat zichtbaar');
		$fields[] = new RequiredIntField('aantal_voorstellen', $model->aantal_voorstellen ?? 0, 'Aantal voorstellen', 0, 10);
		$fields[] = new RequiredIntField('aantal_stemmen', $model->aantal_stemmen ?? 1, 'Aantal stemmen', 0, 10);
		$fields[] = new DateTimeField('sluitingsdatum', $model->sluitingsdatum, 'Sluitingsdatum');
		$fields[] = new RechtenField('rechten_stemmen', $model->rechten_stemmen, 'Rechten stemmen');
		$fields['rechten_mod'] = new RechtenField('rechten_mod', $model->rechten_mod, 'Rechten bewerken');
		$fields['rechten_mod']->title = 'Een peiling mag altijd bewerkt worden door jou, de BASFCie, de PubCie en het bestuur.';


		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

}
