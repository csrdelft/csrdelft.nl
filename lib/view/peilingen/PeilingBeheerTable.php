<?php

namespace CsrDelft\view\peilingen;
use CsrDelft\model\peilingen\PeilingenModel;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\knop\ConfirmDataTableKnop;
use CsrDelft\view\formulier\datatable\knop\DataTableKnop;
use CsrDelft\view\formulier\datatable\Multiplicity;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/10/2018
 */
class PeilingBeheerTable extends DataTable
{
	public function __construct()
	{
		parent::__construct(PeilingenModel::ORM, '/peilingen/beheer', 'Peilingen beheer');

		$this->hideColumn('id', false);

		$this->searchColumn('titel');
		$this->searchColumn('beschrijving');

		$this->setOrder(['id' => 'desc']);

		$this->addKnop(new DataTableKnop(Multiplicity::One(), '/peilingen/bewerken', 'Bewerken', 'Deze peiling bewerken', 'pencil'));
		$this->addKnop(new DataTableKnop(Multiplicity::Zero(), '/peilingen/nieuw', 'Nieuw', 'Nieuwe peiling aanmaken', 'add'));
		$this->addKnop(new ConfirmDataTableKnop(Multiplicity::One(), '/peilingen/verwijderen', 'Verwijderen', 'Peiling verwijderen', 'delete'));
	}
}
