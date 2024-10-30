<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\repository\maalcie\MaaltijdAanmeldingenRepository;
use CsrDelft\repository\maalcie\MaaltijdenRepository;
use CsrDelft\service\maalcie\MaaltijdAanmeldingenService;
use Exception;
use Symfony\Component\Routing\Attribute\Route;

class ApiMaaltijdenController extends AbstractController
{
	public function __construct(
		private readonly MaaltijdenRepository $maaltijdenRepository,
		private readonly MaaltijdAanmeldingenService $maaltijdAanmeldingenService,
		private readonly MaaltijdAanmeldingenRepository $maaltijdAanmeldingenRepository
	) {
	}

	/**
	 * @Auth(P_MAAL_IK)
	 */
	#[Route(path: '/API/2.0/maaltijden/{id}/aanmelden', methods: ['POST'])]
	public function maaltijdAanmelden($id)
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
	 * @Auth(P_MAAL_IK)
	 */
	#[Route(path: '/API/2.0/maaltijden/{id}/afmelden', methods: ['POST'])]
	public function maaltijdAfmelden($id)
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
