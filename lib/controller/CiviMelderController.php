<?php

namespace CsrDelft\controller;

use CsrDelft\controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use CsrDelft\common\Annotation\Auth;

use CsrDelft\entity\civimelder\Activiteit;

use CsrDelft\repository\civimelder\ActiviteitRepository;
use CsrDelft\repository\civimelder\DeelnemerRepository;
use CsrDelft\repository\civimelder\ReeksRepository;


class CiviMelderController extends AbstractController
{
	/** @var ActiviteitRepository */
	private $activiteitRepository;
	/** @var DeelnemerRepository */
//	private $deelnemerRepository;
	/** @var ReeksRepository */
	private $reeksRepository;

	public function __construct(
		ActiviteitRepository $activiteitRepository
//		, DeelnemerRepository $deelnemerRepository
		, ReeksRepository $reeksRepository
	) {
		$this->activiteitRepository = $activiteitRepository;
//		$this->deelnemerRepository = $deelnemerRepository;
		$this->reeksRepository = $reeksRepository;
	}
	/**
	 * @return Response
	 * @Route("/civimelder", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function mijnActiviteiten()
	{
//		return new Response("Test 123 ");
		$alleActiviteiten = $this->activiteitRepository->getKomendeActiviteiten();
		return $this->render('civimelder/mijn_activiteiten.html.twig', [
			'activiteiten' => $alleActiviteiten
		]);
	}
}
