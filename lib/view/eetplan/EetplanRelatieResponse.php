<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\model\entity\eetplan\EetplanBekenden;
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
	 * @return string
	 */
	public function renderElement($entity) {
		$array = $entity->jsonSerialize();
		$noviet1 = $entity->getNoviet1();
		$noviet2 = $entity->getNoviet2();
		$array['noviet1'] = new DataTableColumn($noviet1->getLink('volledig'), $noviet1->achternaam, $noviet1->getNaam('volledig'));
		$array['noviet2'] = new DataTableColumn($noviet2->getLink('volledig'), $noviet2->achternaam, $noviet2->getNaam('volledig'));
		return $array;
	}
}
