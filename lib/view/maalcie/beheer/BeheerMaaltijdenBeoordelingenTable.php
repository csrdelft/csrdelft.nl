<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\DataTable;

class BeheerMaaltijdenBeoordelingenTable extends DataTable {
	/**
	 * BeheerMaaltijdenBeoordeelingenView constructor.
	 */
	public function __construct() {
		parent::__construct(Maaltijd::class, '/maaltijden/beheer/beoordelingen');

		$this->hideColumn('mlt_repetitie_id');
		$this->hidecolumn('product_id');
		$this->hideColumn('aanmeld_limiet');
		$this->hideColumn('gesloten');
		$this->deleteColumn('laatst_gesloten');
		$this->hideColumn('verwijderd');
		$this->hideColumn('aanmeld_filter');
		$this->hideColumn('omschrijving');
		$this->hideColumn('verwerkt');

		$this->addColumn('titel');
		$this->addColumn('tijd', 'titel');
		$this->addColumn('datum', 'tijd');

		$this->addColumn('aanmeldingen', null, null, CellRender::Aanmeldingen());

		// Beoordeling
		$this->addColumn('kwalikok(s)', null, null, null, null, null, 'koks');
		$this->addColumn('aantal_beoordelingen');
		$this->addColumn('kwantiteit');
		$this->addColumn('kwaliteit');
		$this->addColumn('kwantiteit_afwijking');
		$this->addColumn('kwaliteit_afwijking');

		// Sorteren
		$this->setOrder(array('datum' => 'desc'));

		// Doorzoekbaar
		$this->searchColumn('titel');
		$this->searchColumn('datum');
	}

	public function getBreadcrumbs() {
		return "Maaltijden / Beheer / Beoordelingen";
	}
}
