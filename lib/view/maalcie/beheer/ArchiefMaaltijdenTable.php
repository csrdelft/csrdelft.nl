<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\model\maalcie\ArchiefMaaltijdModel;
use CsrDelft\view\formulier\datatable\DataTable;

class ArchiefMaaltijdenTable extends DataTable {
	public function __construct() {
		parent::__construct(ArchiefMaaltijdModel::ORM, '/maaltijden/beheer/archief');
		$this->addColumn('prijs', null, null, 'prijs_render', null, 'num-fmt');
	}

	public function getJavascript() {
		return /** @lang JavaScript */
			parent::getJavascript() . <<<JS
function prijs_render(data) {
	return "€" + (data/100).toFixed(2);
}
JS;

	}
}
