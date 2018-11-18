<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\model\bibliotheek\BoekModel;
use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableResponse;

class BibliotheekCatalogusDatatableResponse extends DataTableResponse {

	/**
	 * @param Boek $entity
	 * @return false|string
	 */
	public function getJson($entity) {
		$arr = $entity->jsonSerialize();
		$arr['titel_link'] = "<a href='{$entity->getUrl()}'>$entity->titel</a>";
		$arr['recensie_count'] = sizeof($entity->getRecensies());
		return json_encode($arr);
	}


}
