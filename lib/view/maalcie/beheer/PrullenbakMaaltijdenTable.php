<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableKnop;

class PrullenbakMaaltijdenTable extends DataTable
{
    public function __construct()
    {
        parent::__construct(MaaltijdenModel::ORM, '/maaltijden/beheer/prullenbak');

        $this->hideColumn('verwijderd');
        $this->hideColumn('aanmeld_limiet');
        $this->hideColumn('omschrijving');

        $this->addColumn('aanmeld_filter', null, null, 'aanmeldFilter_render');
        $this->addColumn('gesloten', null, null, 'gesloten_render');
        $this->addColumn('aanmeldingen', 'aanmeld_limiet', null, 'aanmeldingen_render');
        $this->addColumn('prijs', null, null, 'prijs_render', null, 'num-fmt');

        $this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/maaltijden/beheer/herstel', '', 'Herstellen', 'Deze maaltijd herstellen', 'arrow_undo'));
        $this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/corvee/beheer/maaltijd/:maaltijd_id', '', 'Corvee bewerken', 'Gekoppelde corveetaken bewerken', 'chart_organisation', 'url'));

        $this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/maaltijden/lijst/:maaltijd_id', '', 'Maaltijdlijst', 'Maaltijdlijst bekijken', 'table_normal', 'popup'));

        $this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/maaltijden/beheer/verwijder', '', 'Verwijderen', 'Maaltijd definitief verwijderen', 'cross', 'confirm'));
    }

    public function getJavascript()
    {
        return /** @lang JavaScript */
            parent::getJavascript() . <<<JS
function aanmeldFilter_render(data) {
	return data ? '<span class="ico group_key" title="Aanmeld filter actief: \'' + data + '\'"></span>' : '';
}

function gesloten_render(data) {
    return '<span class="ico '+(data==='1'?'tick':'cross')+'"></span>';
}

function aanmeldingen_render(data, type, row) {
	return row.aantal_aanmeldingen + " (" + row.aanmeld_limiet + ")"; 
}

function prijs_render(data) {
	return "€" + (data/100).toFixed(2);
}
JS;

    }

    public function getBreadcrumbs()
    {
        return "Maaltijden / Beheer / Prullenbak";
    }
}
