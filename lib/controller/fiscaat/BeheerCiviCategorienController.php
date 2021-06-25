<?php

namespace CsrDelft\controller\fiscaat;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\repository\fiscaat\CiviCategorieRepository;
use CsrDelft\view\fiscaat\CiviCategorieSuggestiesResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class BeheerCiviCategorienController {
	/** @var CiviCategorieRepository */
	private $civiCategorieRepository;

	public function __construct(CiviCategorieRepository $civiCategorieRepository) {
		$this->civiCategorieRepository = $civiCategorieRepository;
	}

	/**
	 * @param Request $request
	 * @return CiviCategorieSuggestiesResponse
	 * @Route("/fiscaat/categorien/suggesties", methods={"GET"})
	 * @Auth(P_FISCAAT_READ)
	 */
	public function suggesties(Request $request) {
		$suggesties = $this->civiCategorieRepository->suggesties(sql_contains($request->query->get('q')));

		return new CiviCategorieSuggestiesResponse($suggesties);
	}
}
