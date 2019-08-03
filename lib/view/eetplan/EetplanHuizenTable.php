<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\view\datatable\CellRender;
use CsrDelft\view\datatable\DataTable;
use CsrDelft\view\datatable\knoppen\DataTableKnop;
use CsrDelft\view\datatable\knoppen\DataTableRowKnop;
use CsrDelft\view\datatable\Multiplicity;

class EetplanHuizenTable extends DataTable {
	public function __construct() {
		parent::__construct(EetplanHuizenData::class, '/eetplan/woonoorden', 'Woonoorden die meedoen');
		$this->settings['buttons'] = [];
		$this->selectEnabled = false;
		$this->searchColumn('naam');
		$this->addColumn('eetplan', null, null, CellRender::Check());
		$this->addRowKnop(new DataTableRowKnop($this->dataUrl . '/toggle', 'Woonoord aan/af melden voor eetplan', 'arrow_refresh'));
	}
}
