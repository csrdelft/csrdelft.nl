<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\model\entity\eetplan\Eetplan;
use CsrDelft\view\datatable\DataTableResponse;

/**
 * Data voor EetplanBekendeHuizenTable op /eetplan/bekendehuizen
 *
 * Class EetplanBekendeHuizenResponse
 */
class EetplanBekendeHuizenResponse extends DataTableResponse {
	/**
	 * @param Eetplan $entity
	 * @return string[]
	 */
	public function renderElement($entity) {
		$array = $entity->jsonSerialize();
		$array['woonoord'] = $entity->getWoonoord()->naam;
		$array['naam'] = $entity->getNoviet()->getNaam();

		return $array;
	}
}
