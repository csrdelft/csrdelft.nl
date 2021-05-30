<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\civimelder\Activiteit;
use CsrDelft\entity\civimelder\Reeks;
use CsrDelft\entity\profiel\Profiel;
use CsrDelft\repository\ProfielRepository;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use CsrDelft\common\Annotation\Auth;

use CsrDelft\repository\civimelder\ActiviteitRepository;
use CsrDelft\repository\civimelder\DeelnemerRepository;
use CsrDelft\repository\civimelder\ReeksRepository;

class CiviMelderController extends AbstractController {
	/**
	 * @var ProfielRepository
	 */
	private $profielRepository;
	/**
	 * @var DeelnemerRepository
	 */
	private $deelnemerRepository;
	/**
	 * @var ActiviteitRepository
	 */
	private $activiteitRepository;
	/**
	 * @var ReeksRepository
	 */
	private $reeksRepository;

	public function __construct(ProfielRepository $profielRepository,
															DeelnemerRepository $deelnemerRepository,
															ActiviteitRepository $activiteitRepository,
															ReeksRepository $reeksRepository)
	{
		$this->profielRepository = $profielRepository;
		$this->deelnemerRepository = $deelnemerRepository;
		$this->activiteitRepository = $activiteitRepository;
		$this->reeksRepository = $reeksRepository;
	}

	/**
	 * @param Reeks $reeks
	 * @return Response
	 * @Route("/civimelder/{reeks}", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function mijnActiviteiten(Reeks $reeks) {
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
	public function aanmelden(Request $request, Activiteit $activiteit) {
		$lid = $this->getGegevenLid($activiteit, $request);
		$aantal = $request->request->getInt('aantal', 1);
		$deelnemer = $this->deelnemerRepository->aanmelden($activiteit, $lid, $aantal);

		return $this->render('civimelder/mijn_activiteiten_lijst.html.twig', [
			'activiteit' => $activiteit,
		]);
	}

	/**
	 * @param Request $request
	 * @param Activiteit $activiteit
	 * @return Response
	 * @throws ORMException
	 * @Route("/civimelder/afmelden/{activiteit}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function afmelden(Request $request, Activiteit $activiteit) {
		$lid = $this->getGegevenLid($activiteit, $request);
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
	public function gasten(Request $request, Activiteit $activiteit) {
		$lid = $this->getGegevenLid($activiteit, $request);
		$aantal = $request->request->getInt('aantal', 1);
		$this->deelnemerRepository->aantalAanpassen($activiteit, $lid, $aantal + 1);

		return $this->render('civimelder/mijn_activiteiten_lijst.html.twig', [
			'activiteit' => $activiteit,
		]);
	}

	/**
	 * @param Request $request
	 * @return Profiel|null
	 */
	private function getGegevenLid(Activiteit $activiteit, Request $request) {
		if ($request->request->has('lid') && $activiteit->magLijstBeheren()) {
			$lid = $this->profielRepository->find($request->request->getAlnum('lid'));
			if (!$lid) {
				throw new CsrGebruikerException("Lid niet gevonden.");
			}
		} else {
			$lid = $this->getProfiel();
		}

		return $lid;
	}
}