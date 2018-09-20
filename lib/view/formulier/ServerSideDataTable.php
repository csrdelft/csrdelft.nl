<?php

namespace CsrDelft\view\formulier;

use CsrDelft\view\formulier\datatable\DataTable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 20/09/2018
 */
class ServerSideDataTable extends DataTable {
	public function __construct($orm, $dataUrl, $titel = false, $groupByColumn = null) {
		parent::__construct($orm, $dataUrl, $titel, $groupByColumn);

		$this->settings['serverSide'] = true;
	}
}
