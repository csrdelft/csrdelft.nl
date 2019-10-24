<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\model\fiscaat\CiviCategorieModel;
use CsrDelft\view\fiscaat\CiviCategorieSuggestiesResponse;
use Symfony\Component\HttpFoundation\Request;


/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class BeheerCiviCategorienController {
	/** @var CiviCategorieModel */
	private $model;

	public function __construct() {
		$this->model = CiviCategorieModel::instance();
	}

	public function suggesties(Request $request) {
		$query = '%' . $request->query->get('q') . '%';
		return new CiviCategorieSuggestiesResponse($this->model->find('type LIKE ?', [$query]));
	}
}
