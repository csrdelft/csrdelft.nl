<?php

namespace App\Eetplan\Data;

use App\Eetplan\Models\EetplanBekenden;
use CsrDelft\view\formulier\datatable\DataTableResponse;

/**
 * View voor EetplanBekendenTable op /eetplan/novietrelatie
 *
 * Class EetplanRelatieView
 */
class EetplanBekendenResponse extends DataTableResponse {
	/**
	 * @param EetplanBekenden $entity
	 *
	 * @return string
	 */
	public function getJson($entity) {
		$array = $entity->jsonSerialize();
		$array['noviet1'] = $entity->noviet1->getNaam();
		$array['noviet2'] = $entity->noviet2->getNaam();
		return parent::getJson($array);
	}
}
