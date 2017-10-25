<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\model\entity\maalcie\Maaltijd;
use CsrDelft\view\formulier\datatable\DataTableResponse;

class FiscaatMaaltijdenOverzichtResponse extends DataTableResponse {
	/**
	 * @param Maaltijd $entity
	 *
	 * @return string
	 */
	public function getJson($entity) {
		$data = $entity->jsonSerialize();
		$data['totaal'] = $entity->getPrijs() * $entity->getAantalAanmeldingen();
		return parent::getJson($data);
	}
}
