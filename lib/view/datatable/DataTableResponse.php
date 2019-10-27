<?php

namespace CsrDelft\view\datatable;

use CsrDelft\view\JsonLijstResponse;
use CsrDelft\view\JsonResponse;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 07/05/2017
 */
abstract class DataTableResponse extends JsonLijstResponse {

	public $autoUpdate = false;
	public $modal = null;

	public function getModel() {
		return [
		"modal"=>$this->modal,
		"autoUpdate" => $this->autoUpdate,
		"lastUpdate" => time() - 1,
		"data" => parent::getModel()];
	}
}
