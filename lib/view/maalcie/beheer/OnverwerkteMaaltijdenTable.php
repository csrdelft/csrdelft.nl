<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableKnop;

class OnverwerkteMaaltijdenTable extends DataTable {
	public function __construct() {
		parent::__construct(MaaltijdenModel::ORM, '/maaltijden/beheer?filter=onverwerkt');

		$this->hideColumn('verwerkt');
		$this->hideColumn('gesloten');
		$this->hideColumn('verwijderd');
		$this->hideColumn('aanmeld_limiet');
		$this->hideColumn('omschrijving');
		$this->hideColumn('aanmeld_filter');
		$this->hideColumn('mlt_repetitie_id');

		$this->addColumn('repetitie_naam', 'titel');
		$this->addColumn('aanmeldingen', 'aanmeld_limiet', null, 'aanmeldingen_render');
		$this->addColumn('prijs', null, null, 'prijs_render', null, 'num-fmt');
		$this->addColumn('totaalprijs', null, null, 'totaal_prijs', null, 'num-fmt');

		$this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/maaltijden/fiscaat/verwerk', '', 'Verwerken', 'Maaltijd verwerken', 'cog_go'));

		$this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/maaltijden/beheer/verwijder', '', 'Verwijderen', 'Maaltijd verwijderen', 'cross', 'confirm'));
		$this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/maaltijden/lijst/:maaltijd_id', '', 'Maaltijdlijst', 'Maaltijdlijst bekijken', 'table_normal', 'popup'));

		$aanmeldingen = new DataTableKnop('== 1', $this->dataTableId, '', '', 'Aanmeldingen', 'Aanmeldingen bewerken', 'user', 'defaultCollection');
		$aanmeldingen->addKnop(new DataTableKnop('', $this->dataTableId, '/maaltijden/beheer/aanmelden', '', 'Toevoegen', 'Aanmelding toevoegen', 'user_add'));
		$aanmeldingen->addKnop(new DataTableKnop('', $this->dataTableId, '/maaltijden/beheer/afmelden', '', 'Verwijderen', 'Aanmelding verwijderen', 'user_delete'));

		$this->addKnop($aanmeldingen);

	}

	public function getJavascript() {
		return /** @lang JavaScript */
			parent::getJavascript() . <<<JS
function aanmeldingen_render(data, type, row) {
	return row.aantal_aanmeldingen + " (" + row.aanmeld_limiet + ")"; 
}

function prijs_render(data) {
	return "€" + (data/100).toFixed(2);
}

function totaal_prijs(data, type, row) {
    return prijs_render(row.aantal_aanmeldingen * parseInt(row.prijs));
}
JS;

	}
}
