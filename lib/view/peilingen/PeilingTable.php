<?php

namespace CsrDelft\view\peilingen;

use CsrDelft\entity\peilingen\Peiling;
use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\ConfirmDataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/10/2018
 */
class PeilingTable extends DataTable
{
	public function __construct()
	{
		parent::__construct(
			Peiling::class,
			'/peilingen/beheer',
			'Peilingen beheer'
		);

		$this->hideColumn('id', false);
		$this->hideColumn('rechten_stemmen');

		$this->addColumn('resultaat_zichtbaar', null, null, CellRender::Check());
		$this->addColumn('mag_bewerken', null, null, CellRender::Check());

		$this->searchColumn('titel');
		$this->searchColumn('beschrijving');

		$this->setOrder(['id' => 'desc']);

		$this->addKnop(
			new DataTableKnop(
				Multiplicity::One(),
				'/peilingen/bewerken',
				'Bewerken',
				'Deze peiling bewerken',
				'bewerken'
			)
		);
		$this->addKnop(
			new DataTableKnop(
				Multiplicity::Zero(),
				'/peilingen/nieuw',
				'Nieuw',
				'Nieuwe peiling aanmaken',
				'toevoegen'
			)
		);
		$this->addKnop(
			new ConfirmDataTableKnop(
				Multiplicity::One(),
				'/peilingen/verwijderen',
				'Verwijderen',
				'Peiling verwijderen',
				'verwijderen'
			)
		);
	}
}
