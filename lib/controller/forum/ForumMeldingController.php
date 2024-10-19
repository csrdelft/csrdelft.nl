<?php

namespace CsrDelft\controller\forum;

use Symfony\Component\Routing\Attribute\Route;
use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumDraadMeldingNiveau;
use CsrDelft\repository\forum\ForumDelenMeldingRepository;
use CsrDelft\repository\forum\ForumDradenMeldingRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class ForumMeldingController extends AbstractController
{
	public function __construct(
		private readonly ForumDradenMeldingRepository $forumDradenMeldingRepository,
		private readonly ForumDelenMeldingRepository $forumDelenMeldingRepository
	) {
	}

	/**
	 * Niveau voor meldingen instellen.
	 *
	 * @param ForumDraad $draad
	 * @param string $niveau
	 *
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/forum/meldingsniveau/{draad_id}/{niveau}', methods: ['POST'])]
	public function meldingsniveau(ForumDraad $draad, $niveau)
	{
		if (!$draad || !$draad->magLezen() || !$draad->magMeldingKrijgen()) {
			throw $this->createAccessDeniedException(
				'Onderwerp mag geen melding voor ontvangen worden'
			);
		}
		if (!ForumDraadMeldingNiveau::isValidValue($niveau)) {
			throw $this->createAccessDeniedException(
				'Ongeldig meldingsniveau gespecificeerd'
			);
		}
		$this->forumDradenMeldingRepository->setNiveauVoorLid(
			$draad,
			ForumDraadMeldingNiveau::from($niveau)
		);
		return new JsonResponse(true);
	}

	/**
	 * Niveau voor meldingen deelforum instellen
	 *
	 * @param ForumDeel $deel
	 * @param string $niveau
	 *
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/forum/deelmelding/{forum_id}/{niveau}', methods: ['POST'])]
	public function deelmelding(ForumDeel $deel, $niveau)
	{
		if (!$deel || !$deel->magLezen() || !$deel->magMeldingKrijgen()) {
			throw $this->createAccessDeniedException(
				'Deel mag geen melding voor ontvangen worden'
			);
		}
		if ($niveau !== 'aan' && $niveau !== 'uit') {
			throw $this->createAccessDeniedException(
				'Ongeldig meldingsniveau gespecificeerd'
			);
		}
		$this->forumDelenMeldingRepository->setMeldingVoorLid(
			$deel,
			$niveau === 'aan'
		);
		return new JsonResponse(true);
	}
}
