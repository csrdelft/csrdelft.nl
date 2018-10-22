<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\model\bibliotheek\BiebCatalogus;
use CsrDelft\model\bibliotheek\BoekModel;
use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\datatable\DataTable;
use CsrDelft\view\formulier\datatable\DataTableResponse;

class BibliotheekCatalogusDatatableContent extends DataTableResponse {

	/**
	 * @param Boek $entity
	 * @return false|string
	 */
	public function getJson($entity) {
		$arr = (array) $entity;
		$arr['titel_link'] = "<a href='{$entity->getUrl()}'>$entity->titel</a>";
		return json_encode($arr);
	}


}
