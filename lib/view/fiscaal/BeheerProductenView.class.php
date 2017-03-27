<?php

class BeheerProductenView extends DataTable {
	public function __construct() {
		parent::__construct(MaalcieProduct::class, '/fiscaat/producten', 'Productenbeheer');

		$this->addColumn('prijs');

		$this->addKnop(new DataTableKnop('== 0', $this->dataTableId, '/fiscaat/producten/toevoegen', 'post', 'Nieuw product', 'Nieuw product toevoegen', 'add'));
	}
}

class NieuwProductForm extends ModalForm {
	function __construct(MaalcieProduct $model) {
		parent::__construct($model, '/fiscaat/producten/toevoegen', false, true);
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