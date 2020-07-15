<?php

namespace CsrDelft\controller;
use CsrDelft\common\Annotation\Auth;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\model\entity\LidStatus;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\renderer\TemplateView;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Deze controller is een een controller voor een presentielijst die tijdens de hv's gebruikt kan worden.
 *
 * @author R.M.Huizer
 * @since 15/7/2020
 */
class PresentielijstController extends AbstractController {
	public function __construct() {
	}

	/**
	 * @return Response
	 * @param ProfielRepository
	 * @Route("/presentielijst", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function laatZien(ProfielRepository $profielRepository) {
		$resultaat = $profielRepository->findByLidStatus(Lidstatus::getHvLidLike());

		///TODO implementeer sortering volgens lidstatus
		return view('tools.presentielijst', ['leden' => $resultaat])->toResponse();
	}
}
