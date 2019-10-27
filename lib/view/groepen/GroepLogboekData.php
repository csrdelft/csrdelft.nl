<?php

namespace CsrDelft\view\groepen;

use CsrDelft\model\ProfielModel;
use CsrDelft\view\datatable\DataTableResponse;

class GroepLogboekData extends DataTableResponse {

	public function renderElement($log) {
		$array = $log->jsonSerialize();

		$array['uid'] = ProfielModel::getLink($log->uid, 'civitas');

		return $array;
	}

}
