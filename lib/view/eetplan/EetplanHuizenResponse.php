<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\model\entity\groepen\Woonoord;
use CsrDelft\view\datatable\DataTableResponse;

/**
 * Data voor EetplanHuizenTable op /eetplan/woonoorden
 *
 * Class EetplanHuizenView
 */
class EetplanHuizenResponse extends DataTableResponse {
	/**
	 * @param Woonoord $entity
	 *
	 * @return string
	 */
	public function getJson($entity) {
		return parent::getJson(array(
			'UUID' => $entity->getUUID(),
			'id' => $entity->id,
			'naam' => '<a href="' . $entity->getUrl() . '">' . $entity->naam . '</a>',
			'soort' => $entity->soort,
			'eetplan' => $entity->eetplan
		));
	}
}
