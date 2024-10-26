<?php

namespace CsrDelft\controller\api;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\repository\forum\ForumDradenGelezenRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\forum\ForumDelenService;
use CsrDelft\view\bbcode\CsrBB;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ApiForumController extends AbstractController
{
	public function __construct(
		private readonly ForumDradenGelezenRepository $forumDradenGelezenRepository,
		private readonly ForumDelenService $forumDelenService,
		private readonly ForumPostsRepository $forumPostsRepository,
		private readonly ForumDradenRepository $forumDradenRepository
	) {
	}

	/**
	 * @Auth(P_OUDLEDEN_READ)
	 * @return JsonResponse
	 */
	#[Route(path: '/API/2.0/forum/recent', methods: ['GET'])]
	public function getRecent()
	{
		$offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT) ?: 0;
		$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 10;

		$draden = $this->forumDelenService->getRecenteForumDraden(
			$limit,
			null,
			false,
			$offset
		);

		foreach ($draden as $draad) {
			$draad->ongelezen = $draad->getAantalOngelezenPosts();
			$draad->laatste_post = $this->forumPostsRepository->get(
				$draad->laatste_post_id
			);
			$draad->laatste_wijziging_naam = ProfielRepository::getNaam(
				$draad->laatste_wijziging_uid,
				'civitas'
			);
		}

		return new JsonResponse(['data' => array_values($draden)]);
	}

	/**
	 * @Auth(P_OUDLEDEN_READ)
	 * @param int offset
	 * @param int limit
	 * @return JsonResponse
	 */
	#[Route(path: '/API/2.0/forum/onderwerp/{id}', methods: ['GET'])]
	public function getOnderwerp($id)
	{
		$offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT) ?: 0;
		$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 10;

		try {
			$draad = $this->forumDradenRepository->get((int) $id);
		} catch (Exception) {
			throw $this->createNotFoundException();
		}

		if (!$draad->magLezen()) {
			throw $this->createAccessDeniedException();
		}

		$this->forumDradenGelezenRepository->setWanneerGelezenDoorLid(
			$draad,
			date_create_immutable()
		);

		$posts = $this->forumPostsRepository->findBy(
			['draad_id' => $id, 'wacht_goedkeuring' => false, 'verwijderd' => false],
			['datum_tijd' => 'DESC'],
			$limit,
			$offset
		);

		// Most recent first
		$posts = array_reverse($posts);

		foreach ($posts as $post) {
			$post->uid_naam = ProfielRepository::getNaam($post->uid, 'civitas');
			$post->tekst = CsrBB::parseLight($post->tekst);
		}

		return new JsonResponse(['data' => $posts]);
	}
}
