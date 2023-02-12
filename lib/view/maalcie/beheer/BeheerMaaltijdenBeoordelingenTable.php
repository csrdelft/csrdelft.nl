<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\entity\maalcie\MaaltijdBeoordelingDTO;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\DataTable;

class BeheerMaaltijdenBeoordelingenTable extends DataTable
{
	/**
	 * BeheerMaaltijdenBeoordeelingenView constructor.
	 */
	public function __construct()
	{
		parent::__construct(
			MaaltijdBeoordelingDTO::class,
			'/maaltijden/beheer/beoordelingen'
		);

		$this->addColumn('titel');
		$this->addColumn('tijd', 'titel');
		$this->addColumn('datum', 'tijd');

		$this->addColumn(
			'aanmeldingen',
			'kwantiteit',
			null,
			CellRender::Aanmeldingen()
		);

		// Beoordeling
		$this->addColumn(
			'kwalikok(s)',
			'kwantiteit',
			null,
			null,
			null,
			null,
			'koks'
		);
		$this->addColumn('aantal_beoordelingen', 'kwantiteit');

		// Sorteren
		$this->setOrder(['datum' => 'desc']);

		// Doorzoekbaar
		$this->searchColumn('titel');
		$this->searchColumn('datum');
	}

	public function getBreadcrumbs()
	{
		return 'Maaltijden / Beheer / Beoordelingen';
	}
}
