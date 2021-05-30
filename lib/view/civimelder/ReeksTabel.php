<?php

namespace CsrDelft\view\civimelder;

use CsrDelft\entity\civimelder\Reeks;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

class ReeksTabel extends DataTable {
	public function __construct() {
		parent::__construct(Reeks::class, '/civimelder/beheer', 'CiviMelder beheer', null, false);

		$this->addColumn('naam');

		$this->setOrder(['naam' => 'asc']);
		$this->searchColumn('naam');

		$this->addKnop(new DataTableKnop(Multiplicity::One(), '/civimelder/beheer/reeks/bewerken', 'Bewerken', 'Deze reeks bewerken', 'pencil'));
//		$this->addKnop(new DataTableKnop(Multiplicity::Zero(), '/peilingen/nieuw', 'Nieuw', 'Nieuwe peiling aanmaken', 'add'));
//		$this->addKnop(new ConfirmDataTableKnop(Multiplicity::One(), '/peilingen/verwijderen', 'Verwijderen', 'Peiling verwijderen', 'delete'));
	}
}
