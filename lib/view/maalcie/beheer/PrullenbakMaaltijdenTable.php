<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\view\formulier\datatable\CellRender;
use CsrDelft\view\formulier\datatable\CellType;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\knop\DataTableKnop;
use CsrDelft\view\formulier\datatable\knop\PopupDataTableKnop;
use CsrDelft\view\formulier\datatable\knop\UrlDataTableKnop;
use CsrDelft\view\formulier\datatable\knop\ConfirmDataTableKnop;
use CsrDelft\view\formulier\datatable\Multiplicity;

class PrullenbakMaaltijdenTable extends DataTable {
	public function __construct() {
		parent::__construct(MaaltijdenModel::ORM, '/maaltijden/beheer/prullenbak');

		$this->hideColumn('verwijderd');
		$this->hideColumn('aanmeld_limiet');
		$this->hideColumn('omschrijving');

		$this->addColumn('aanmeld_filter', null, null, CellRender::AanmeldFilter());
		$this->addColumn('gesloten', null, null, CellRender::Check());
		$this->addColumn('aanmeldingen', 'aanmeld_limiet', null, CellRender::Aanmeldingen());
		$this->addColumn('prijs', null, null, CellRender::Bedrag(), null, CellType::FormattedNumber());

		$this->addKnop(new DataTableKnop(Multiplicity::One(), '/maaltijden/beheer/herstel', 'Herstellen', 'Deze maaltijd herstellen', 'arrow_undo'));
		$this->addKnop(new UrlDataTableKnop(Multiplicity::One(), '/corvee/beheer/maaltijd/:maaltijd_id', 'Corvee bewerken', 'Gekoppelde corveetaken bewerken', 'chart_organisation'));

		$this->addKnop(new PopupDataTableKnop(Multiplicity::One(), '/maaltijden/lijst/:maaltijd_id', 'Maaltijdlijst', 'Maaltijdlijst bekijken', 'table_normal'));

		$this->addKnop(new ConfirmDataTableKnop(Multiplicity::One(), '/maaltijden/beheer/verwijder', 'Verwijderen', 'Maaltijd definitief verwijderen', 'cross'));
	}

	public function getBreadcrumbs() {
		return "Maaltijden / Beheer / Prullenbak";
	}
}
