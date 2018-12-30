<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\model\maalcie\MaaltijdenModel;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\DataTable;

class BeheerMaaltijdenBeoordelingenTable extends DataTable {
	/**
	 * BeheerMaaltijdenBeoordeelingenView constructor.
	 */
	public function __construct() {
		parent::__construct(MaaltijdenModel::ORM, '/maaltijden/beheer/beoordelingen');

		$this->hideColumn('verwijderd');
		$this->hideColumn('aanmeld_limiet');
		$this->hideColumn('omschrijving');
		$this->hideColumn('mlt_repetitie_id');
		$this->hideColumn('laatst_gesloten');
		$this->hidecolumn('maaltijd_id');
		$this->hidecolumn('product_id');
		$this->hideColumn('repetitie_naam');
		$this->hideColumn('aanmeld_filter');
		$this->hideColumn('gesloten');
		$this->hideColumn('verwerkt');
        $this->hideColumn('prijs');

        $this->addColumn('aanmeldingen', null, null, CellRender::Aanmeldingen());

		// Beoordeling
        $this->addColumn('kwantiteit');
        $this->addColumn('kwantiteit_afwijking');
        $this->addColumn('kwantiteit_aantal');
        $this->addColumn('kwaliteit');
        $this->addColumn('kwaliteit_afwijking');
        $this->addColumn('kwaliteit_aantal');

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
