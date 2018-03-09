<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\model\maalcie\ArchiefMaaltijdModel;
use CsrDelft\view\formulier\datatable\CellRender;
use CsrDelft\view\formulier\datatable\CellType;
use CsrDelft\view\formulier\datatable\DataTable;

class ArchiefMaaltijdenTable extends DataTable {
	public function __construct() {
		parent::__construct(ArchiefMaaltijdModel::ORM, '/maaltijden/beheer/archief');
		$this->addColumn('prijs', null, null, CellRender::Bedrag(), null, CellType::FormattedNumber());
	}
}
