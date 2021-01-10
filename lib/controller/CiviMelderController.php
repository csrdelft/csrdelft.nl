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
	 * @var ActiviteitRepository
	 */
	private $activiteitRepository;
	/**
	 * @var ReeksRepository
	 */
	private $reeksRepository;

	public function __construct(ProfielRepository $profielRepository,
															ActiviteitRepository $activiteitRepository,
															ReeksRepository $reeksRepository)
	{
		$this->profielRepository = $profielRepository;
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
		$lid = $this->getGegevenLid($request);
		$aantal = $request->request->getInt('aantal', 1);
		$deelnemer = $this->deelnemerRepository->aanmelden($activiteit, $lid, $aantal);

		return $this->json(true);
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
		$lid = $this->getGegevenLid($request);
		$this->deelnemerRepository->afmelden($activiteit, $lid);

		return $this->json(true);
	}

	/**
	 * @param Request $request
	 * @param Activiteit $activiteit
	 * @return Response
	 * @throws ORMException
	 * @Route("/civimelder/aantal/{activiteit}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function aantal(Request $request, Activiteit $activiteit) {
		$lid = $this->getGegevenLid($request);
		$aantal = $request->request->getInt('aantal', 1);
		$this->deelnemerRepository->aantalAanpassen($activiteit, $lid, $aantal);

		return $this->json(true);
	}

	/**
	 * @param Request $request
	 * @return Profiel|null
	 */
	private function getGegevenLid(Request $request) {
		if ($request->request->has('lid')) {
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
