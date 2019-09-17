<?php


namespace CsrDelft\view\declaratie;


use CsrDelft\model\ProfielModel;
use CsrDelft\view\datatable\DataTableResponse;

class DeclaratieResponse extends DataTableResponse {

	public function getJson($entity) {
		$arr = parent::getJson($entity);
		$arr['naam'] = ProfielModel::getNaam($entity->uid);
		return $arr;
	}
}
