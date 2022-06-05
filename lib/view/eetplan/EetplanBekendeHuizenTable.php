<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\entity\eetplan\Eetplan;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use CsrDelft\view\datatable\Multiplicity;

class EetplanBekendeHuizenTable extends DataTable
{
	public function __construct()
	{
		parent::__construct(
			Eetplan::class,
			'/eetplan/bekendehuizen',
			'Novieten die huizen kennen'
		);

		$this->selectEnabled = false;

		$this->hideColumn('avond');
		$this->addColumn('naam', 'opmerking');
		$this->addColumn('woonoord', 'naam');

		$this->searchColumn('woonoord');
		$this->searchColumn('naam');

		$this->addKnop(
			new DataTableKnop(
				Multiplicity::Zero(),
				$this->dataUrl . '/toevoegen',
				'Toevoegen',
				'Bekende toevoegen',
				'toevoegen'
			)
		);
		$this->addRowKnop(
			new DataTableRowKnop(
				$this->dataUrl . '/verwijderen',
				'Bekende verwijderen',
				'verwijderen'
			)
		);
		$this->addRowKnop(
			new DataTableRowKnop(
				$this->dataUrl . '/bewerken',
				'Opmerking bewerken',
				'pencil'
			)
		);
	}
}
