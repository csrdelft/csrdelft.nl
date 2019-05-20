<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\controller\framework\QueryParamTrait;
use CsrDelft\model\fiscaat\CiviCategorieModel;
use CsrDelft\view\fiscaat\CiviCategorieSuggestiesResponse;


/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class BeheerCiviCategorienController {
	use QueryParamTrait;
	/** @var CiviCategorieModel */
	private $model;

	public function __construct() {
		$this->model = CiviCategorieModel::instance();
	}

	public function suggesties() {
		$query = '%' . $this->getParam('q') . '%';
		return new CiviCategorieSuggestiesResponse($this->model->find('type LIKE ?', [$query]));
	}
}
