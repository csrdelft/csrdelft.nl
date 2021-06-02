<?php

namespace CsrDelft\controller;

use CsrDelft\entity\aanmelder\AanmeldActiviteit;
use CsrDelft\entity\aanmelder\Reeks;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use CsrDelft\common\Annotation\Auth;

use CsrDelft\repository\aanmelder\AanmeldActiviteitRepository;
use CsrDelft\repository\aanmelder\DeelnemerRepository;

class AanmelderController extends AbstractController {
	/**
	 * @var DeelnemerRepository
	 */
	private $deelnemerRepository;
	/**
	 * @var AanmeldActiviteitRepository
	 */
	private $activiteitRepository;

	public function __construct(DeelnemerRepository $deelnemerRepository,
															AanmeldActiviteitRepository $activiteitRepository)
	{
		$this->deelnemerRepository = $deelnemerRepository;
		$this->activiteitRepository = $activiteitRepository;
	}

	/**
	 * @param Reeks $reeks
	 * @return Response
	 * @Route("/aanmelder/{reeks}", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function mijnActiviteiten(Reeks $reeks): Response
	{
		$alleActiviteiten = $this->activiteitRepository->getKomendeActiviteiten($reeks);
		return $this->render('aanmelder/mijn_activiteiten.html.twig', [
			'reeks' => $reeks
			, 'activiteiten' => $alleActiviteiten
		]);
	}

	/**
	 * @param Request $request
	 * @param AanmeldActiviteit $activiteit
	 * @return Response
	 * @throws ORMException
	 * @Route("/aanmelder/aanmelden/{activiteit}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function aanmelden(Request $request, AanmeldActiviteit $activiteit): Response
	{
		$lid = $this->getProfiel();
		$aantal = $request->request->getInt('aantal', 1);
		$this->deelnemerRepository->aanmelden($activiteit, $lid, $aantal);

		return $this->render('aanmelder/mijn_activiteiten_lijst.html.twig', [
			'activiteit' => $activiteit,
		]);
	}

	/**
	 * @param AanmeldActiviteit $activiteit
	 * @return Response
	 * @throws ORMException
	 * @Route("/aanmelder/afmelden/{activiteit}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function afmelden(AanmeldActiviteit $activiteit): Response
	{
		$lid = $this->getProfiel();
		$this->deelnemerRepository->afmelden($activiteit, $lid);

		return $this->render('aanmelder/mijn_activiteiten_lijst.html.twig', [
			'activiteit' => $activiteit,
		]);
	}

	/**
	 * @param Request $request
	 * @param AanmeldActiviteit $activiteit
	 * @return Response
	 * @throws ORMException
	 * @Route("/aanmelder/gasten/{activiteit}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function gasten(Request $request, AanmeldActiviteit $activiteit): Response
	{
		$lid = $this->getProfiel();
		$aantal = $request->request->getInt('aantal', 1);
		$this->deelnemerRepository->aantalAanpassen($activiteit, $lid, $aantal + 1);

		return $this->render('aanmelder/mijn_activiteiten_lijst.html.twig', [
			'activiteit' => $activiteit,
		]);
	}
}
