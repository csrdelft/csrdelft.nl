<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\Multiplicity;

class EetplanHuizenTable extends DataTable {
	public function __construct() {
		parent::__construct(EetplanHuizenData::class, '/eetplan/woonoorden/', 'Woonoorden die meedoen');
		$this->searchColumn('naam');
		$this->addColumn('eetplan', null, null, CellRender::Check());
		$this->addKnop(new DataTableKnop(Multiplicity::Any(), $this->dataUrl . 'aan', 'Aanmelden', 'Woonoorden aanmelden voor eetplan', 'add'));
		$this->addKnop(new DataTableKnop(Multiplicity::Any(), $this->dataUrl . 'uit', 'Afmelden', 'Woonoorden afmelden voor eetplan', 'delete'));
	}
}
