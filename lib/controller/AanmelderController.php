<?php

namespace CsrDelft\controller;

use CsrDelft\entity\aanmelder\AanmeldActiviteit;
use CsrDelft\entity\aanmelder\Reeks;
use CsrDelft\repository\aanmelder\ReeksRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use CsrDelft\common\Annotation\Auth;

use CsrDelft\repository\aanmelder\AanmeldActiviteitRepository;
use CsrDelft\repository\aanmelder\DeelnemerRepository;

class AanmelderController extends AbstractController
{
	/**
	 * @var DeelnemerRepository
	 */
	private $deelnemerRepository;
	/**
	 * @var AanmeldActiviteitRepository
	 */
	private $activiteitRepository;

	public function __construct(
		DeelnemerRepository $deelnemerRepository,
		AanmeldActiviteitRepository $activiteitRepository
	) {
		$this->deelnemerRepository = $deelnemerRepository;
		$this->activiteitRepository = $activiteitRepository;
	}

	/**
  * @param ReeksRepository $reeksRepository
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/aanmelder', methods: ['GET'])]
 public function mijnActiviteiten(ReeksRepository $reeksRepository): Response
	{
		$reeksen = [];
		foreach ($reeksRepository->findAll() as $reeks) {
			$activiteiten = $this->activiteitRepository->getKomendeActiviteiten(
				$reeks
			);
			if ($activiteiten->count() > 0) {
				$reeksen[] = [
					'reeks' => $reeks,
					'activiteiten' => $activiteiten,
				];
			}
		}

		return $this->render('aanmelder/mijn_activiteiten.html.twig', [
			'reeksen' => $reeksen,
		]);
	}

	/**
  * @param Reeks $reeks
  * @return Response
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/aanmelder/{reeks}', methods: ['GET'])]
 public function reeksActiviteiten(Reeks $reeks): Response
	{
		$alleActiviteiten = $this->activiteitRepository->getKomendeActiviteiten(
			$reeks
		);
		return $this->render('aanmelder/reeks_overzicht.html.twig', [
			'reeks' => $reeks,
			'activiteiten' => $alleActiviteiten,
		]);
	}

	/**
  * @param Request $request
  * @param AanmeldActiviteit $activiteit
  * @return Response
  * @throws ORMException
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/aanmelder/aanmelden/{activiteit}', methods: ['POST'])]
 public function aanmelden(
		Request $request,
		AanmeldActiviteit $activiteit
	): Response {
		$lid = $this->getProfiel();
		$aantal = $request->request->getInt('aantal', 1);
		$this->deelnemerRepository->aanmelden($activiteit, $lid, $aantal);

		return $this->render('aanmelder/onderdelen/activiteit_regel.html.twig', [
			'activiteit' => $activiteit,
		]);
	}

	/**
  * @throws OptimisticLockException
  * @throws ORMException
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/aanmelder/aanmelden/{activiteit}', methods: ['GET'])]
 public function aanmeldenBB(AanmeldActiviteit $activiteit): Response
	{
		$this->deelnemerRepository->aanmelden($activiteit, $this->getProfiel(), 1);

		return $this->render('aanmelder/bb_activiteit.html.twig', [
			'activiteit' => $activiteit,
		]);
	}

	/**
  * @param AanmeldActiviteit $activiteit
  * @return Response
  * @throws ORMException
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/aanmelder/afmelden/{activiteit}', methods: ['POST'])]
 public function afmelden(AanmeldActiviteit $activiteit): Response
	{
		$lid = $this->getProfiel();
		$this->deelnemerRepository->afmelden($activiteit, $lid);

		return $this->render('aanmelder/onderdelen/activiteit_regel.html.twig', [
			'activiteit' => $activiteit,
		]);
	}

	/**
  * @throws OptimisticLockException
  * @throws ORMException
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/aanmelder/afmelden/{activiteit}', methods: ['GET'])]
 public function afmeldenBB(AanmeldActiviteit $activiteit): Response
	{
		$this->deelnemerRepository->afmelden($activiteit, $this->getProfiel());

		return $this->render('aanmelder/bb_activiteit.html.twig', [
			'activiteit' => $activiteit,
		]);
	}

	/**
  * @param Request $request
  * @param AanmeldActiviteit $activiteit
  * @return Response
  * @throws ORMException
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/aanmelder/gasten/{activiteit}', methods: ['POST'])]
 public function gasten(
		Request $request,
		AanmeldActiviteit $activiteit
	): Response {
		$lid = $this->getProfiel();
		$aantal = $request->request->getInt('aantal', 1);
		$this->deelnemerRepository->aantalAanpassen($activiteit, $lid, $aantal + 1);

		return $this->render('aanmelder/onderdelen/activiteit_regel.html.twig', [
			'activiteit' => $activiteit,
		]);
	}
}
