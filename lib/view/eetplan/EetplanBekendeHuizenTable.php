<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\model\eetplan\EetplanModel;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableKnop;

class EetplanBekendeHuizenTable extends DataTable {
	public function __construct() {
		parent::__construct(EetplanModel::ORM, '/eetplan/bekendehuizen/', 'Novieten die huizen kennen');
		$this->hideColumn('avond');
		$this->hideColumn('woonoord_id');
		$this->hideColumn('uid');
		$this->addColumn('woonoord');
		$this->addColumn('naam');

		$this->addKnop(new DataTableKnop("== 0", $this->dataTableId, $this->dataUrl . 'toevoegen', 'post popup', 'Toevoegen', 'Bekende toevoegen', 'toevoegen'));
		$this->addKnop(new DataTableKnop("== 1", $this->dataTableId, $this->dataUrl . 'verwijderen', 'post confirm', 'Verwijderen', 'Bekende verwijderen', 'verwijderen'));
	}
}
