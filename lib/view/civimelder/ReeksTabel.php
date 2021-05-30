<?php

namespace CsrDelft\view\civimelder;

use CsrDelft\entity\civimelder\Reeks;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\ConfirmDataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

class ReeksTabel extends DataTable {
	public function __construct() {
		parent::__construct(Reeks::class, '/civimelder/beheer', 'CiviMelder beheer', null, false);

		$this->addColumn('naam');

		$this->setOrder(['naam' => 'asc']);
		$this->searchColumn('naam');

		if (Reeks::magAanmaken()) {
			$this->addKnop(new DataTableKnop(Multiplicity::None(), '/civimelder/beheer/reeks/nieuw', 'Nieuw', 'Nieuwe reeks aanmaken', 'add'));
		}
		$this->addKnop(new DataTableKnop(Multiplicity::One(), '/civimelder/beheer/reeks/bewerken', 'Bewerken', 'Deze reeks bewerken', 'pencil'));
		if (Reeks::magAanmaken()) {
			$this->addKnop(new ConfirmDataTableKnop(Multiplicity::One(), '/civimelder/beheer/reeks/verwijderen', 'Verwijderen', 'Activiteit verwijderen', 'cross'));
		}
	}
}
