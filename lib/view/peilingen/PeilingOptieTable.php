<?php

namespace CsrDelft\view\peilingen;
use CsrDelft\entity\peilingen\PeilingOptie;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use CsrDelft\view\datatable\Multiplicity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/10/2018
 */
class PeilingOptieTable extends DataTable
{
	public function __construct($id)
	{
		parent::__construct(PeilingOptie::class, '/peilingen/opties/' . $id, null);

		$this->hideColumn('peiling_id');

		$this->searchColumn('titel');
		$this->searchColumn('beschrijving');

		$this->addKnop(new DataTableKnop(Multiplicity::None(), '/peilingen/opties/' . $id . '/toevoegen', 'Toevoegen', 'Optie toevoegen', 'add'));
		$this->addRowKnop(new DataTableRowKnop('/peilingen/opties/verwijderen', 'Optie Verwijderen', 'verwijderen'));
	}

}
