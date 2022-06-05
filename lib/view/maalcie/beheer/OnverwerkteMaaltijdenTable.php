<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\CellType;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\CollectionDataTableKnop;
use CsrDelft\view\datatable\knoppen\ConfirmDataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\PopupDataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

class OnverwerkteMaaltijdenTable extends DataTable {
	public function __construct() {
		parent::__construct(Maaltijd::class, '/maaltijden/beheer?filter=onverwerkt');

		$this->hideColumn('verwerkt');
		$this->hideColumn('gesloten');
		$this->hideColumn('verwijderd');
		$this->hideColumn('aanmeld_limiet');
		$this->hideColumn('omschrijving');
		$this->hideColumn('aanmeld_filter');
		$this->hideColumn('mlt_repetitie_id');

		$this->addColumn('repetitie_naam', 'titel');
		$this->addColumn('aanmeldingen', 'aanmeld_limiet', null, CellRender::Aanmeldingen());
		$this->addColumn('prijs', null, null, CellRender::Bedrag(), null, CellType::FormattedNumber());
		$this->addColumn('totaalprijs', null, null, CellRender::TotaalPrijs(), null, CellType::FormattedNumber());

		$this->addKnop(new DataTableKnop(Multiplicity::One(), '/maaltijden/fiscaat/verwerk', 'Verwerken', 'Maaltijd verwerken', 'gear'));

		$this->addKnop(new ConfirmDataTableKnop(Multiplicity::One(), '/maaltijden/beheer/verwijder', 'Verwijderen', 'Maaltijd verwijderen', 'verwijderen'));
		$this->addKnop(new PopupDataTableKnop(Multiplicity::One(), '/maaltijden/lijst/:maaltijd_id', 'Maaltijdlijst', 'Maaltijdlijst bekijken', 'tabel'));

		$aanmeldingen = new CollectionDataTableKnop(Multiplicity::One(), 'Aanmeldingen', 'Aanmeldingen bewerken', 'user-pen');
		$aanmeldingen->addKnop(new DataTableKnop(Multiplicity::None(), '/maaltijden/beheer/aanmelden', 'Toevoegen', 'Aanmelding toevoegen', 'user-plus'));
		$aanmeldingen->addKnop(new DataTableKnop(Multiplicity::None(), '/maaltijden/beheer/afmelden', 'Verwijderen', 'Aanmelding verwijderen', 'user-minus'));

		$this->addKnop($aanmeldingen);

	}
}
