<?php

namespace CsrDelft\controller;

use CsrDelft\entity\civimelder\Activiteit;
use CsrDelft\entity\civimelder\Reeks;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use CsrDelft\common\Annotation\Auth;

use CsrDelft\repository\civimelder\ActiviteitRepository;
use CsrDelft\repository\civimelder\DeelnemerRepository;

class CiviMelderController extends AbstractController {
	/**
	 * @var DeelnemerRepository
	 */
	private $deelnemerRepository;
	/**
	 * @var ActiviteitRepository
	 */
	private $activiteitRepository;

	public function __construct(DeelnemerRepository $deelnemerRepository,
															ActiviteitRepository $activiteitRepository)
	{
		$this->deelnemerRepository = $deelnemerRepository;
		$this->activiteitRepository = $activiteitRepository;
	}

	/**
	 * @param Reeks $reeks
	 * @return Response
	 * @Route("/civimelder/{reeks}", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function mijnActiviteiten(Reeks $reeks): Response
	{
		$alleActiviteiten = $this->activiteitRepository->getKomendeActiviteiten($reeks);
		return $this->render('civimelder/mijn_activiteiten.html.twig', [
			'reeks' => $reeks
			, 'activiteiten' => $alleActiviteiten
		]);
	}

	/**
	 * @param Request $request
	 * @param Activiteit $activiteit
	 * @return Response
	 * @throws ORMException
	 * @Route("/civimelder/aanmelden/{activiteit}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function aanmelden(Request $request, Activiteit $activiteit): Response
	{
		$lid = $this->getProfiel();
		$aantal = $request->request->getInt('aantal', 1);
		$this->deelnemerRepository->aanmelden($activiteit, $lid, $aantal);

		return $this->render('civimelder/mijn_activiteiten_lijst.html.twig', [
			'activiteit' => $activiteit,
		]);
	}

	/**
	 * @param Activiteit $activiteit
	 * @return Response
	 * @throws ORMException
	 * @Route("/civimelder/afmelden/{activiteit}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function afmelden(Activiteit $activiteit): Response
	{
		$lid = $this->getProfiel();
		$this->deelnemerRepository->afmelden($activiteit, $lid);

		return $this->render('civimelder/mijn_activiteiten_lijst.html.twig', [
			'activiteit' => $activiteit,
		]);
	}

	/**
	 * @param Request $request
	 * @param Activiteit $activiteit
	 * @return Response
	 * @throws ORMException
	 * @Route("/civimelder/gasten/{activiteit}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function gasten(Request $request, Activiteit $activiteit): Response
	{
		$lid = $this->getProfiel();
		$aantal = $request->request->getInt('aantal', 1);
		$this->deelnemerRepository->aantalAanpassen($activiteit, $lid, $aantal + 1);

		return $this->render('civimelder/mijn_activiteiten_lijst.html.twig', [
			'activiteit' => $activiteit,
		]);
	}
}
