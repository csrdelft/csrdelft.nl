<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\view\datatable\DataTableResponse;

class FiscaatMaaltijdenOverzichtResponse extends DataTableResponse {
	/**
	 * @param Maaltijd $entity
	 *
	 */
	public function renderElement($entity) {
		$data = $entity->jsonSerialize();
		$data['totaal'] = $entity->getPrijs() * $entity->getAantalAanmeldingen();
		return $data;
	}
}
