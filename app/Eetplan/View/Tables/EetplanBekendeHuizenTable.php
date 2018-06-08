<?php

namespace App\Eetplan\View\Tables;

use App\Eetplan\Models\Eetplan;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\knop\DataTableKnop;
use CsrDelft\view\formulier\datatable\Multiplicity;

class EetplanBekendeHuizenTable extends DataTable {
	public function __construct() {
		parent::__construct(Eetplan::class, '/eetplan/beheer/bekendehuizen', 'Novieten die huizen kennen');
		$this->hideColumn('avond');
		$this->hideColumn('woonoord_id');
		$this->hideColumn('uid');
		$this->addColumn('woonoord');
		$this->addColumn('naam');

		$this->addKnop(new DataTableKnop(Multiplicity::Zero(), $this->dataUrl . '/toevoegen', 'Toevoegen', 'Bekende toevoegen', 'toevoegen'));
		$this->addKnop(new DataTableKnop(Multiplicity::One(), $this->dataUrl . '/verwijderen', 'Verwijderen', 'Bekende verwijderen', 'verwijderen'));
	}
}
