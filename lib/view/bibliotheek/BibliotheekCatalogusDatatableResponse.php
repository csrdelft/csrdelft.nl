<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\view\datatable\DataTableResponse;

class BibliotheekCatalogusDatatableResponse extends DataTableResponse {

	/**
	 * @param Boek $entity
	 */
	public function renderElement($entity) {
		$arr = $entity->jsonSerialize();
		$arr['titel_link'] = "<a href='{$entity->getUrl()}'>$entity->titel</a>";
		$arr['recensie_count'] = sizeof($entity->getRecensies());
		return $arr;
	}


}
