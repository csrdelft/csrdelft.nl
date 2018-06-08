<?php

namespace App\Eetplan\Data;

use CsrDelft\model\entity\groepen\Woonoord;
use CsrDelft\view\formulier\datatable\DataTableResponse;

/**
 * Data voor EetplanHuizenTable op /eetplan/woonoorden
 *
 * Class EetplanHuizenView
 */
class EetplanHuisResponse extends DataTableResponse {
	/**
	 * @param Woonoord $entity
	 *
	 * @return string
	 */
	public function getJson($entity) {
		return parent::getJson([
			'UUID' => $entity->getUUID(),
			'id' => $entity->id,
			'naam' => $entity->naam,
			'soort' => $entity->soort,
			'eetplan' => $entity->eetplan
        ]);
	}
}
