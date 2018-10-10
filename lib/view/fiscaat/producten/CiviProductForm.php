<?php

namespace CsrDelft\view\fiscaat\producten;

use CsrDelft\model\entity\fiscaat\CiviCategorie;
use CsrDelft\model\entity\fiscaat\CiviProduct;
use CsrDelft\model\fiscaat\CiviCategorieModel;
use CsrDelft\view\formulier\getalvelden\IntField;
use CsrDelft\view\formulier\getalvelden\RequiredBedragField;
use CsrDelft\view\formulier\getalvelden\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\RequiredEntityField;
use CsrDelft\view\formulier\invoervelden\RequiredTextField;
use CsrDelft\view\formulier\keuzevelden\RequiredJaNeeField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */
class CiviProductForm extends ModalForm {
	function __construct(CiviProduct $model, $target) {
		parent::__construct($model, '/fiscaat/producten/' . $target, false, true);

		$categorie = CiviCategorieModel::instance()->find('id = ?', array($model->categorie_id))->fetch();
		if ($categorie == false) {
			$categorie = new CiviCategorie();
		}
		$fields = [];
		$fields['id'] = new IntField('id', $model->id, 'id');
		$fields['id']->hidden = true;
		$fields[] = new RequiredIntField('status', $model->status, 'Status');
		$fields[] = new RequiredTextField('beschrijving', $model->beschrijving, 'Beschrijving');
		$fields[] = new RequiredIntField('prioriteit', $model->prioriteit, 'Prioriteit');
		$fields[] = new RequiredJaNeeField('beheer', $model->beheer, 'Beheer');
		$fields[] = new RequiredBedragField('prijs', $model->prijs, 'Prijs', '€', 0, 50, 0.50);
		$fields[] = new RequiredEntityField('categorie', 'type', 'Categorie', CiviCategorieModel::instance(), '/fiscaat/categorien/suggesties?q=', $categorie);

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen();
	}
}
