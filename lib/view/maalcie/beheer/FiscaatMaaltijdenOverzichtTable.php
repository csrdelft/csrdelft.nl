<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableKnop;

class FiscaatMaaltijdenOverzichtTable extends DataTable {
	public function __construct() {
		parent::__construct(MaaltijdenModel::ORM, '/maaltijden/fiscaat/overzicht');

		$this->deleteColumn('mlt_repetitie_id');
		$this->deleteColumn('product_id');
		$this->deleteColumn('aanmeld_limiet');
		$this->deleteColumn('gesloten');
		$this->deleteColumn('laatst_gesloten');
		$this->deleteColumn('verwijderd');
		$this->deleteColumn('verwerkt');
		$this->deleteColumn('aanmeld_filter');
		$this->deleteColumn('omschrijving');

		$this->addColumn('aantal_aanmeldingen');
		$this->addColumn('prijs', null, null, 'prijs_render', null, 'num-fmt');
		$this->addColumn('totaal', null, null, 'prijs_render', null, 'num-fmt');

		$this->setOrder(array('datum' => 'desc'));

		$this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/maaltijden/lijst/:maaltijd_id', '', 'Maaltijdlijst', 'Maaltijdlijst bekijken', 'table_normal', 'popup'));
	}

	public function getJavascript() {
		return /** @lang JavaScript */
			parent::getJavascript() . <<<JS
function prijs_render(data) {
	return "€" + (data/100).toFixed(2);
}
JS;

	}
}
