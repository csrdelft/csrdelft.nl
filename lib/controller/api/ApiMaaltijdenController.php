<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\service\maalcie\MaaltijdAanmeldingenService;
use Exception;
use Symfony\Component\Routing\Annotation\Route;

class ApiMaaltijdenController extends AbstractController
{
	private $maaltijdenRepository;
	private $maaltijdAanmeldingenRepository;
	/**
	 * @var MaaltijdAanmeldingenService
	 */
	private $maaltijdAanmeldingenService;

	public function __construct(
		MaaltijdenRepository $maaltijdenRepository,
		MaaltijdAanmeldingenService $maaltijdAanmeldingenService,
		MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository
	) {
		$this->maaltijdenRepository = $maaltijdenRepository;
		$this->maaltijdAanmeldingenRepository = $maaltijdAanmeldingenRepository;
		$this->maaltijdAanmeldingenService = $maaltijdAanmeldingenService;
	}

	/**
	 * @Route("/API/2.0/maaltijden/{id}/aanmelden", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function maaltijdAanmelden($id): array
	{
		try {
			$maaltijd = $this->maaltijdenRepository->getMaaltijd($id);
			$aanmelding = $this->maaltijdAanmeldingenService->aanmeldenVoorMaaltijd(
				$maaltijd,
				$this->getProfiel(),
				$this->getProfiel()
			);
			return ['data' => $aanmelding->maaltijd];
		} catch (Exception $e) {
			throw $this->createAccessDeniedException($e->getMessage());
		}
	}

	/**
	 * @Route("/API/2.0/maaltijden/{id}/afmelden", methods={"POST"})
	 * @Auth(P_MAAL_IK)
	 */
	public function maaltijdAfmelden($id): array
	{
		try {
			$maaltijd = $this->maaltijdenRepository->getMaaltijd($id);
			$this->maaltijdAanmeldingenService->afmeldenDoorLid(
				$maaltijd,
				$this->getProfiel()
			);
			return ['data' => $maaltijd];
		} catch (Exception $e) {
			throw $this->createAccessDeniedException($e->getMessage());
		}
	}
}
