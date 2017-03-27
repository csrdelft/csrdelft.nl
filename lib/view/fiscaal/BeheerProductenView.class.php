<?php

class BeheerProductenView extends DataTable {
	public function __construct() {
		parent::__construct(MaalcieProduct::class, '/fiscaat/producten', 'Productenbeheer');

		$this->addColumn('prijs');
		$this->addColumn('beheer', 'prijs', null, 'truefalse');

		$this->addKnop(new DataTableKnop('== 0', $this->dataTableId, '/fiscaat/producten/toevoegen', 'post', 'Nieuw', 'Nieuw product toevoegen', 'add'));
		$this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/fiscaat/producten/bewerken', 'post', 'Bewerken', 'Product bewerken', 'pencil'));
		$this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/fiscaat/producten/verwijderen', 'post', 'Verwijderen', 'Product verwijderen', 'cross'));
	}

	public function getJavascript() {
		return parent::getJavascript() . <<<JS
function truefalse (data) {
    return '<span class="ico '+(data?'tick':'cross')+'"></span>';
}
JS;

	}
}

class CiviProductForm extends ModalForm {
	function __construct(MaalcieProduct $model, $target) {
		parent::__construct($model, '/fiscaat/producten/' . $target, false, true);
		$fields['id'] = new IntField('id', $model->id, 'id');
		$fields['id']->hidden = true;
		$fields[] = new RequiredIntField('status', $model->status, 'Status');
		$fields[] = new RequiredTextField('beschrijving', $model->beschrijving, 'Beschrijving');
		$fields[] = new RequiredIntField('prioriteit', $model->prioriteit, 'Prioriteit');
		$fields[] = new RequiredJaNeeField('beheer', $model->beheer, 'Beheer');
		$fields[] = new RequiredBedragField('prijs', $model->prijs, 'Prijs', '€', 0, 50, 0.50);
		$fields['btn'] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}
}

class BeheerProductenResponse extends DataTableResponse {}