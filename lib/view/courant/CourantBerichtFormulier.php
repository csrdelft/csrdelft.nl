<?php


namespace CsrDelft\view\courant;


use CsrDelft\model\entity\courant\CourantBericht;
use CsrDelft\model\entity\courant\CourantCategorie;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\BBCodeField;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

class CourantBerichtFormulier extends Formulier {
	/**
	 * CourantFormulier constructor.
	 * @param CourantBericht $model
	 * @param $action
	 */
	public function __construct($model, $action) {
		parent::__construct($model, $action, 'Courant bericht');

		$fields = [];

		$fields[] = new TextField('titel', $model->titel, 'Titel');
		$fields[] = new SelectField('cat', $model->cat, 'Categorie', CourantCategorie::getSelectOptions());
		$fields[] = new BBCodeField('bericht', $model->bericht, 'Bericht');
		$fields[] = new HiddenField('volgorde', $model->volgorde, '');

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}

}
