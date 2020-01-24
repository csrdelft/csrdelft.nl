<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\entity\eetplan\EetplanBekenden;
use CsrDelft\view\datatable\DataTableColumn;
use CsrDelft\view\datatable\DataTableResponse;

/**
 * View voor EetplanBekendenTable op /eetplan/novietrelatie
 *
 * Class EetplanRelatieView
 */
class EetplanRelatieResponse extends DataTableResponse {
	/**
	 * @param EetplanBekenden $entity
	 *
	 * @return array
	 */
	public function renderElement($entity) {
		$noviet1 = $entity->noviet1;
		$noviet2 = $entity->noviet2;
		return [
				'noviet1' => new DataTableColumn($noviet1->getLink('volledig'), $noviet1->achternaam, $noviet1->getNaam('volledig')),
				'noviet2' => new DataTableColumn($noviet2->getLink('volledig'), $noviet2->achternaam, $noviet2->getNaam('volledig')),
			] + (array)$entity;
	}
}
