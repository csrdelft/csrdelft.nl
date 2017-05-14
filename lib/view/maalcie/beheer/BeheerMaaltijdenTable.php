<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\model\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableKnop;

class BeheerMaaltijdenTable extends DataTable
{
    /**
     * BeheerMaaltijdenView constructor.
     *
     * @param $repetities MaaltijdRepetitie[]
     */
    public function __construct($repetities)
    {
        parent::__construct(MaaltijdenModel::ORM, '/maaltijden/beheer');

        $this->hideColumn('verwijderd');
        $this->hideColumn('aanmeld_limiet');
        $this->hideColumn('omschrijving');
        $this->hideColumn('mlt_repetitie_id');

        $this->addColumn('repetitie_naam', 'titel');
        $this->addColumn('aanmeld_filter', null, null, 'aanmeldFilter_render');
        $this->addColumn('gesloten', null, null, 'check_render');
        $this->addColumn('verwerkt', null, null, 'check_render');
        $this->addColumn('aanmeldingen', 'aanmeld_limiet', null, 'aanmeldingen_render');
        $this->addColumn('prijs', null, null, 'prijs_render', null, 'num-fmt');

        $this->setOrder(array('datum' => 'asc'));

        $this->searchColumn('titel');
        $this->searchColumn('prijs');
        $this->searchColumn('aanmeld_filter');

        $weergave = new DataTableKnop('', $this->dataTableId, '', '', "Weergave", 'Weergave van tabel', '', 'collection');
        $weergave->addKnop(new DataTableKnop('', $this->dataTableId, '/maaltijden/beheer', '', 'Toekomst', 'Toekomst weergeven', 'time_go', 'sourceChange'));
        $weergave->addKnop(new DataTableKnop('', $this->dataTableId, '/maaltijden/beheer?filter=alles', '', 'Alles', 'Alles weergeven', 'time', 'sourceChange'));
        $this->addKnop($weergave);

        $nieuw = new DataTableKnop('', $this->dataTableId, '', '', 'Nieuw', 'Nieuwe maaltijd aanmaken', 'add', 'collection');

        foreach ($repetities as $repetitie) {
            $nieuw->addKnop(new DataTableKnop('', $this->dataTableId, '/maaltijden/beheer/nieuw?mrid=' . $repetitie->mlt_repetitie_id, '', $repetitie->standaard_titel, "Nieuwe $repetitie->standaard_titel aanmaken"));
        }

        $nieuw->addKnop(new DataTableKnop('', $this->dataTableId, '/maaltijden/beheer/nieuw', '', 'Anders', 'Maaltijd zonder repetitie aanmaken', 'calendar_edit'));
        $this->addKnop($nieuw);

        $this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/maaltijden/beheer/toggle/:maaltijd_id', '', 'Open/Sluit', 'Maaltijd openen of sluiten'));

        $aanmeldingen = new DataTableKnop('== 1', $this->dataTableId, '', '', 'Aanmeldingen', 'Aanmeldingen bewerken', 'user', 'defaultCollection');
        $aanmeldingen->addKnop(new DataTableKnop('', $this->dataTableId, '/maaltijden/beheer/aanmelden', '', 'Toevoegen', 'Aanmelding toevoegen', 'user_add'));
        $aanmeldingen->addKnop(new DataTableKnop('', $this->dataTableId, '/maaltijden/beheer/afmelden', '', 'Verwijderen', 'Aanmelding verwijderen', 'user_delete'));

        $this->addKnop($aanmeldingen);

        $this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/maaltijden/beheer/bewerk', '', 'Bewerken', 'Maaltijd bewerken', 'pencil'));
        $this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/corvee/beheer/maaltijd/:maaltijd_id', '', 'Corvee bewerken', 'Gekoppelde corveetaken bewerken', 'chart_organisation', 'url'));
        $this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/maaltijden/beheer/verwijder', '', 'Verwijderen', 'Maaltijd verwijderen', 'cross', 'confirm'));

        $this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/maaltijden/lijst/:maaltijd_id', '', 'Maaltijdlijst', 'Maaltijdlijst bekijken', 'table_normal', 'popup'));
    }

    public function getJavascript()
    {
        return /** @lang JavaScript */
            parent::getJavascript() . <<<JS
function aanmeldFilter_render(data) {
	return data ? '<span class="ico group_key" title="Aanmeld filter actief: \'' + data + '\'"></span>' : '';
}

function check_render(data) {
    return '<span class="ico '+(data=='1'?'tick':'cross')+'"></span>';
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
        return "Maaltijden / Beheer";
    }
}
