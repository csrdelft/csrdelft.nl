<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\model\entity\eetplan\EetplanBekenden;
use CsrDelft\view\formulier\datatable\DataTableResponse;

/**
 * View voor EetplanBekendenTable op /eetplan/novietrelatie
 *
 * Class EetplanRelatieView
 */
class EetplanRelatieResponse extends DataTableResponse {
	/**
	 * @param EetplanBekenden $entity
	 *
	 * @return string
	 */
	public function getJson($entity) {
		$array = $entity->jsonSerialize();
		$array['noviet1'] = $entity->getNoviet1()->getNaam();
		$array['noviet2'] = $entity->getNoviet2()->getNaam();
		return parent::getJson($array);
	}
}
