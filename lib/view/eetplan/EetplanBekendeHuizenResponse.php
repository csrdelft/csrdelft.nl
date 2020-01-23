<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\entity\eetplan\Eetplan;
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
		return [
			'woonoord' => $entity->getWoonoord()->naam,
			'naam' => $entity->noviet->getNaam(),
			'avond' => $entity->avond->format('d-m-Y'),
		] + (array)$entity;
	}
}
