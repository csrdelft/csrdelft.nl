<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\CellType;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\PopupDataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

class FiscaatMaaltijdenOverzichtTable extends DataTable
{
	public function __construct()
	{
		parent::__construct(Maaltijd::class, '/maaltijden/fiscaat/overzicht');

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
		$this->addColumn('prijs', null, null, CellRender::Bedrag(), null, CellType::FormattedNumber());
		$this->addColumn('totaal', null, null, CellRender::Bedrag(), null, CellType::FormattedNumber());

		$this->setOrder(array('datum' => 'desc'));

		$this->addKnop(new PopupDataTableKnop(Multiplicity::One(), '/maaltijden/lijst/:maaltijd_id', 'Maaltijdlijst', 'Maaltijdlijst bekijken', 'table_normal'));
	}
}
