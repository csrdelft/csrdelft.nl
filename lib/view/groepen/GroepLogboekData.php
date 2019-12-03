<?php

namespace CsrDelft\view\groepen;

use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\datatable\DataTableResponse;

class GroepLogboekData extends DataTableResponse {

	public function renderElement($log) {
		$array = $log->jsonSerialize();

		$array['uid'] = ProfielRepository::getLink($log->uid, 'civitas');

		return $array;
	}

}
