<?php


namespace CsrDelft\view\declaratie;


use CsrDelft\model\entity\Declaratie;
use CsrDelft\view\datatable\DataTable;

class DeclaratieTable extends DataTable {
	public function __construct() {
		parent::__construct(Declaratie::class, '/decla', 'Declaraties');

		$this->addColumn('naam');
	}
}
