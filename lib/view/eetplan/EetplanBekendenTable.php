<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\model\entity\eetplan\EetplanBekenden;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\knop\DataTableKnop;
use CsrDelft\view\formulier\datatable\Multiplicity;

class EetplanBekendenTable extends DataTable {
	public function __construct() {
		parent::__construct(EetplanBekenden::class, '/eetplan/novietrelatie/', 'Novieten die elkaar kennen');
		$this->addColumn('noviet1');
		$this->addColumn('noviet2');
		$this->searchColumn('noviet1');
		$this->searchColumn('noviet2');

		$this->addKnop(new DataTableKnop(Multiplicity::Zero(), $this->dataUrl . 'toevoegen', 'Toevoegen', 'Bekenden toevoegen', 'add'));
		$this->addKnop(new DataTableKnop(Multiplicity::Any(), $this->dataUrl . 'verwijderen', 'Verwijderen', 'Bekenden verwijderen', 'cross'));
	}
}
