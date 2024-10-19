<?php

namespace CsrDelft\controller\fiscaat;

use Symfony\Component\Routing\Attribute\Route;
use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Util\SqlUtil;
use CsrDelft\repository\fiscaat\CiviCategorieRepository;
use CsrDelft\view\fiscaat\CiviCategorieSuggestiesResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class BeheerCiviCategorienController
{
	public function __construct(
		private readonly CiviCategorieRepository $civiCategorieRepository
	) {
	}

	/**
	 * @param Request $request
	 * @return CiviCategorieSuggestiesResponse
	 * @Auth(P_FISCAAT_READ)
	 */
	#[Route(path: '/fiscaat/categorien/suggesties', methods: ['GET'])]
	public function suggesties(Request $request)
	{
		$suggesties = $this->civiCategorieRepository->suggesties(
			SqlUtil::sql_contains($request->query->get('q'))
		);

		return new CiviCategorieSuggestiesResponse($suggesties);
	}
}
