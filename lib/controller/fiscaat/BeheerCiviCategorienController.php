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
	private $civiCategorieModel;

	public function __construct(CiviCategorieModel $civiCategorieModel) {
		$this->civiCategorieModel = $civiCategorieModel;
	}

	public function suggesties(Request $request) {
		$query = '%' . $request->query->get('q') . '%';
		return new CiviCategorieSuggestiesResponse($this->civiCategorieModel->find('type LIKE ?', [$query]));
	}
}
