<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\model\entity\eetplan\Eetplan;
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
	 */
	public function getJson($entity) {
		$array = $entity->jsonSerialize();
		$array['woonoord'] = $entity->getWoonoord()->naam;
		$array['naam'] = $entity->getNoviet()->getNaam();

		return parent::getJson($array);
	}
}
