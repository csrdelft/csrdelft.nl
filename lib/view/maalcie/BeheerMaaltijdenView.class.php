<?php

/**
 * BeheerMaaltijdenView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Toon het maaltijdensysteem menu en een body
 *
 */
class BeheerMaaltijdenView extends SmartyTemplateView {
	public function __construct(View $model, $titel = false) {
		parent::__construct($model, $titel);
	}

	public function view() {
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->model->view();
	}
}

class BeheerMaaltijdenTable extends DataTable {
	/**
	 * BeheerMaaltijdenView constructor.
	 * @param $repetities MaaltijdRepetitie[]
	 */
	public function __construct($repetities) {
		parent::__construct(MaaltijdenModel::ORM, '/maaltijden/beheer');

		$this->hideColumn('verwijderd');
		$this->hideColumn('aanmeld_limiet');
		$this->hideColumn('omschrijving');
		$this->hideColumn('mlt_repetitie_id');
		$this->hideColumn('verwerkt');

		$this->addColumn('repetitie_naam', 'titel');
		$this->addColumn('aanmeld_filter', null, null, 'aanmeldFilter_render');
		$this->addColumn('gesloten', null, null, 'gesloten_render');
		$this->addColumn('aanmeldingen', 'aanmeld_limiet', null, 'aanmeldingen_render');
		$this->addColumn('prijs', null, null, 'prijs_render');

		$this->setOrder(array('datum' => 'desc'));

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

		$lijst = new DataTableKnop('== 1', $this->dataTableId, '', '', 'Lijst', 'Maaltijdlijst bekijken', '', 'defaultCollection');
		$lijst->addKnop(new DataTableKnop('', $this->dataTableId, '/maaltijden/beheer/fiscaat/:maaltijd_id', '', 'Fiscale Maaltijdlijst', 'Fiscale maaltijdlijst bekijken', 'money_euro', 'popup'));
		$lijst->addKnop(new DataTableKnop('', $this->dataTableId, '/maaltijden/lijst/:maaltijd_id', '', 'Maaltijdlijst', 'Maaltijdlijst bekijken', 'table_normal', 'popup'));
		$this->addKnop($lijst);
	}

	public function getJavascript() {
		return /** @lang JavaScript */
			parent::getJavascript() . <<<JS
function aanmeldFilter_render(data) {
	return data ? '<span class="ico group_key" title="Aanmeld filter actief: \'' + data + '\'"></span>' : '';
}

function gesloten_render(data) {
    return '<span class="ico '+(data=='1'?'tick':'cross')+'"></span>';
}

function aanmeldingen_render(data, type, row) {
	return row.aantal_aanmeldingen + " (" + row.aanmeld_limiet + ")"; 
}

function prijs_render(data) {
	
	return "&euro; " + (data/100).toFixed(2).replace('.', ',');
}
JS;

	}

	public function getBreadcrumbs() {
		return "Maaltijden / Beheer";
	}
}

class BeheerMaaltijdenLijst extends DataTableResponse {}

class PrullenbakMaaltijdenTable extends DataTable {
	public function __construct() {
		parent::__construct(MaaltijdenModel::ORM, '/maaltijden/beheer/prullenbak');

		$this->hideColumn('verwijderd');
		$this->hideColumn('aanmeld_limiet');
		$this->hideColumn('omschrijving');

		$this->addColumn('aanmeld_filter', null, null, 'aanmeldFilter_render');
		$this->addColumn('gesloten', null, null, 'gesloten_render');
		$this->addColumn('aanmeldingen', 'aanmeld_limiet', null, 'aanmeldingen_render');
		$this->addColumn('prijs', null, null, 'prijs_render');

		$this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/maaltijden/beheer/herstel', '', 'Herstellen', 'Deze maaltijd herstellen', 'arrow_undo'));
		$this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/corvee/beheer/maaltijd/:maaltijd_id', '', 'Corvee bewerken', 'Gekoppelde corveetaken bewerken', 'chart_organisation', 'url'));

		$lijst = new DataTableKnop('== 1', $this->dataTableId, '', '', 'Lijst', 'Maaltijdlijst bekijken', '', 'defaultCollection');
		$lijst->addKnop(new DataTableKnop('', $this->dataTableId, '/maaltijden/beheer/fiscaat/:maaltijd_id', '', 'Fiscale Maaltijdlijst', 'Fiscale maaltijdlijst bekijken', 'money_euro', 'popup'));
		$lijst->addKnop(new DataTableKnop('', $this->dataTableId, '/maaltijden/lijst/:maaltijd_id', '', 'Maaltijdlijst', 'Maaltijdlijst bekijken', 'table_normal', 'popup'));
		$this->addKnop($lijst);

		$this->addKnop(new DataTableKnop('== 1', $this->dataTableId, '/maaltijden/beheer/verwijder', '', 'Verwijderen', 'Maaltijd definitief verwijderen', 'cross', 'confirm'));
	}

	public function getJavascript() {
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
	
	return "&euro; " + (data/100).toFixed(2).replace('.', ',');
}
JS;

	}

	public function getBreadcrumbs() {
		return "Maaltijden / Beheer / Prullenbak";
	}
}

class ArchiefMaaltijdenTable extends DataTable {
	public function __construct() {
		parent::__construct(ArchiefMaaltijdModel::ORM, '/maaltijden/beheer/archief');
		$this->addColumn('prijs', null, null, 'prijs_render');
	}

	public function getJavascript() {
		return /** @lang JavaScript */
			parent::getJavascript() . <<<JS
function prijs_render(data) {
	return "&euro; " + (data/100).toFixed(2).replace('.', ',');
}
JS;

	}
}

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
		$this->addColumn('prijs', null, null, 'prijs_render');

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
	
	return "&euro; " + (data/100).toFixed(2).replace('.', ',');
}
JS;

	}
}
