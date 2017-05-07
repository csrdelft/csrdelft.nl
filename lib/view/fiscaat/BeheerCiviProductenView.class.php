<?php

namespace CsrDelft\view\fiscaat;

use CsrDelft\model\entity\fiscaat\CiviProduct;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableKnop;

class BeheerCiviProductenView extends DataTable {
	public function __construct() {
		parent::__construct(CiviProduct::class, '/fiscaat/producten', 'Productenbeheer');

		$this->addColumn('prijs', null, null, 'prijs_render', null, 'num-fmt');
		$this->addColumn('beheer', 'prijs', null, 'truefalse');
		$this->hideColumn('prioriteit');

		$this->searchColumn('beschrijving');

		$this->addKnop(new DataTableKnop('== 0', $this->dataTableId, '/fiscaat/producten/toevoegen', 'post', 'Nieuw', 'Nieuw product toevoegen', 'add'));
		$this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/fiscaat/producten/bewerken', 'post', 'Bewerken', 'Product bewerken', 'pencil'));
		$this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/fiscaat/producten/verwijderen', 'post', 'Verwijderen', 'Product verwijderen', 'cross'));
	}

	public function getBreadcrumbs() {
		return '<a href="/" title="Startpagina"><span class="fa fa-home module-icon"></span></a> » <a href="/fiscaat"><span class="fa fa-eur module-icon"></span></a> » <span class="active">Producten</span>';
	}

	public function getJavascript() {
		return /** @lang JavaScript */
			parent::getJavascript() . <<<JS
function truefalse (data) {
    return '<span class="ico '+(data?'tick':'cross')+'"></span>';
}

function prijs_render(data) {
	return "€" + (data/100).toFixed(2);
}
JS;
	}
}
