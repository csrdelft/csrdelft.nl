<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\repository\fiscaat\CiviCategorieRepository;
use CsrDelft\view\fiscaat\CiviCategorieSuggestiesResponse;
use Symfony\Component\HttpFoundation\Request;


/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class BeheerCiviCategorienController {
	/** @var CiviCategorieRepository */
	private $civiCategorieRepository;

	public function __construct(CiviCategorieRepository $civiCategorieRepository) {
		$this->civiCategorieRepository = $civiCategorieRepository;
	}

	public function suggesties(Request $request) {
		$suggesties = $this->civiCategorieRepository->suggesties(sql_contains($request->query->get('q')));

		return new CiviCategorieSuggestiesResponse($suggesties);
	}
}
