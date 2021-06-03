<?php

namespace CsrDelft\view\aanmelder;

use CsrDelft\entity\aanmelder\Reeks;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\ConfirmDataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

class ReeksTabel extends DataTable {
	public function __construct() {
		parent::__construct(Reeks::class, '/aanmelder/beheer', 'Aanmelder beheer', null, false);

		$this->addColumn('id');
		$this->addColumn('naam');

		$this->setOrder(['naam' => 'asc']);
		$this->searchColumn('naam');

		if (Reeks::magAanmaken()) {
			$this->addKnop(new DataTableKnop(Multiplicity::None(), '/aanmelder/beheer/reeks/nieuw', 'Nieuw', 'Nieuwe reeks aanmaken', 'add'));
		}
		$this->addKnop(new DataTableKnop(Multiplicity::One(), '/aanmelder/beheer/reeks/bewerken', 'Bewerken', 'Deze reeks bewerken', 'pencil'));
		if (Reeks::magAanmaken()) {
			$this->addKnop(new ConfirmDataTableKnop(Multiplicity::One(), '/aanmelder/beheer/reeks/verwijderen', 'Verwijderen', 'Activiteit verwijderen', 'cross'));
		}
	}
}
