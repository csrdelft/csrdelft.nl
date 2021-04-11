<?php

namespace CsrDelft\view\peilingen;

use CsrDelft\entity\peilingen\PeilingOptie;
use CsrDelft\view\formulier\invoervelden\ProsemirrorField;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/10/2018
 */
class PeilingOptieForm extends ModalForm
{
	/**
	 * @param PeilingOptie $model
	 * @param int $id
	 */
	public function __construct($model, $id)
	{
		parent::__construct($model,'/peilingen/opties/' . $id . '/toevoegen', 'Optie toevoegen', true);

		$fields = [];
		$fields[] = new HiddenField('peiling_id', $model->peiling_id);
		$fields[] = new RequiredTextField('titel', $model->titel, 'Titel');
		$fields[] = new ProsemirrorField('beschrijving', $model->beschrijving, 'Beschrijving');

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
