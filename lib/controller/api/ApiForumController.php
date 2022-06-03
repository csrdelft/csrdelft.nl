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
use Symfony\Component\Routing\Annotation\Route;

class ApiForumController extends AbstractController {
	private $forumDradenRepository;
	private $forumPostsRepository;
	/**
	 * @var ForumDradenGelezenRepository
	 */
	private $forumDradenGelezenRepository;
	/**
	 * @var ForumDelenService
	 */
	private $forumDelenService;

	public function __construct(ForumDradenGelezenRepository $forumDradenGelezenRepository,
															ForumDelenService $forumDelenService,
															ForumPostsRepository         $forumPostsRepository,
															ForumDradenRepository        $forumDradenRepository)
	{
		$this->forumDradenGelezenRepository = $forumDradenGelezenRepository;
		$this->forumPostsRepository = $forumPostsRepository;
		$this->forumDradenRepository = $forumDradenRepository;
		$this->forumDelenService = $forumDelenService;
	}

	/**
	 * @Route("/API/2.0/forum/recent", methods={"GET"})
	 * @Auth(P_OUDLEDEN_READ)
	 * @return JsonResponse
	 */
	public function getRecent() {
		$offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT) ?: 0;
		$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 10;

		$draden = $this->forumDelenService->getRecenteForumDraden($limit, null, false, $offset);

		foreach ($draden as $draad) {
			$draad->ongelezen = $draad->getAantalOngelezenPosts();
			$draad->laatste_post = $this->forumPostsRepository->get($draad->laatste_post_id);
			$draad->laatste_wijziging_naam = ProfielRepository::getNaam($draad->laatste_wijziging_uid, 'civitas');
		}

		return new JsonResponse(array('data' => array_values($draden)));
	}

	/**
	 * @Route("/API/2.0/forum/onderwerp/{id}", methods={"GET"})
	 * @Auth(P_OUDLEDEN_READ)
	 * @param int offset
	 * @param int limit
	 * @return JsonResponse
	 */
	public function getOnderwerp($id) {
		$offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT) ?: 0;
		$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 10;

		try {
			$draad = $this->forumDradenRepository->get((int)$id);
		} catch (Exception $e) {
			throw $this->createNotFoundException();
		}

		if (!$draad->magLezen()) {
			throw $this->createAccessDeniedException();
		}

		$this->forumDradenGelezenRepository->setWanneerGelezenDoorLid($draad, date_create_immutable());

		$posts = $this->forumPostsRepository->findBy(['draad_id' => $id, 'wacht_goedkeuring' => false, 'verwijderd' => false], ['datum_tijd' => 'DESC'], $limit, $offset);

		// Most recent first
		$posts = array_reverse($posts);

		foreach ($posts as $post) {
			$post->uid_naam = ProfielRepository::getNaam($post->uid, 'civitas');
			$post->tekst = CsrBB::parseLight($post->tekst);
		}

		return new JsonResponse(array('data' => $posts));
	}

}
