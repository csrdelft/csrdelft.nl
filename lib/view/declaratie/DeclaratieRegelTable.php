<?php

namespace CsrDelft\view\declaratie;

use CsrDelft\model\entity\DeclaratieRegel;
use CsrDelft\view\datatable\DataTable;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 26/04/2019
 */
class DeclaratieRegelTable extends DataTable {
	public function __construct($orm, $dataUrl, $titel = false, $groupByColumn = null) {
		parent::__construct(DeclaratieRegel::class, '/declaratie/', $titel, $groupByColumn);
	}
}
