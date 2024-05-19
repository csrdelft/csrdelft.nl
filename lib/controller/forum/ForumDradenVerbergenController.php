<?php

namespace CsrDelft\controller\forum;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\FlashType;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\repository\forum\ForumDradenVerbergenRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ForumDradenVerbergenController extends AbstractController
{
	/**
	 * @var ForumDradenVerbergenRepository
	 */
	private $forumDradenVerbergenRepository;

	public function __construct(
		ForumDradenVerbergenRepository $forumDradenVerbergenRepository
	) {
		$this->forumDradenVerbergenRepository = $forumDradenVerbergenRepository;
	}

	/**
  * Forum draad verbergen in zijbalk.
  *
  * @param ForumDraad $draad
  * @return JsonResponse
  */
 #[Route(path: '/forum/verbergen/{draad_id}', methods: ['POST'])] // @Auth(P_LOGGED_IN)
 public function verbergen(ForumDraad $draad): JsonResponse
	{
		if (!$draad->magVerbergen()) {
			throw new CsrGebruikerException('Onderwerp mag niet verborgen worden');
		}
		if ($draad->isVerborgen()) {
			throw new CsrGebruikerException('Onderwerp is al verborgen');
		}
		$this->forumDradenVerbergenRepository->setVerbergenVoorLid($draad);
		return new JsonResponse(true);
	}

	/**
  * Forum draad tonen in zijbalk.
  *
  * @param ForumDraad $draad
  * @return JsonResponse
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/forum/tonen/{draad_id}', methods: ['POST'])]
 public function tonen(ForumDraad $draad): JsonResponse
	{
		if (!$draad->isVerborgen()) {
			throw new CsrGebruikerException('Onderwerp is niet verborgen');
		}
		$this->forumDradenVerbergenRepository->setVerbergenVoorLid($draad, false);
		return new JsonResponse(true);
	}

	/**
  * Forum draden die verborgen zijn door lid weer tonen.
  * @Auth(P_LOGGED_IN)
  */
 #[Route(path: '/forum/toonalles', methods: ['POST'])]
 public function toonalles(): JsonResponse
	{
		$aantal = $this->forumDradenVerbergenRepository->getAantalVerborgenVoorLid();
		$this->forumDradenVerbergenRepository->toonAllesVoorLeden([
			$this->getUid(),
		]);
		$this->addFlash(
			FlashType::SUCCESS,
			$aantal .
				' onderwerp' .
				($aantal === 1 ? ' wordt' : 'en worden') .
				' weer getoond in de zijbalk'
		);
		return new JsonResponse(true);
	}
}
