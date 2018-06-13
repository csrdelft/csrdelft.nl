<?php

namespace App\Eetplan\Data;

use App\Eetplan\Models\Eetplan;
use CsrDelft\view\formulier\datatable\DataTableResponse;

/**
 * Data voor EetplanBekendeHuizenTable op /eetplan/bekendehuizen
 *
 * Class EetplanBekendeHuizenResponse
 */
class EetplanBekendeHuizenResponse extends DataTableResponse {
    /**
     * @param Eetplan $entity
     *
     * @return string
     * @throws \CsrDelft\common\CsrException
     */
	public function getJson($entity) {
		$array = $entity->jsonSerialize();
		$array['woonoord'] = $entity->woonoord()->naam;
		$array['naam'] = $entity->noviet->getNaam();

		return parent::getJson($array);
	}
}
