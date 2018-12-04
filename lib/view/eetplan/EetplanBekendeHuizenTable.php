<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\model\eetplan\EetplanModel;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

class EetplanBekendeHuizenTable extends DataTable {
	public function __construct() {
		parent::__construct(EetplanModel::ORM, '/eetplan/bekendehuizen/', 'Novieten die huizen kennen');
		$this->hideColumn('avond');
		$this->hideColumn('woonoord_id');
		$this->hideColumn('uid');
		$this->addColumn('woonoord');
		$this->addColumn('naam');

		$this->addKnop(new DataTableKnop(Multiplicity::Zero(), $this->dataUrl . 'toevoegen', 'Toevoegen', 'Bekende toevoegen', 'toevoegen'));
		$this->addKnop(new DataTableKnop(Multiplicity::One(), $this->dataUrl . 'verwijderen', 'Verwijderen', 'Bekende verwijderen', 'verwijderen'));
	}
}
