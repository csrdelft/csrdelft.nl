<?php

namespace CsrDelft\controller\forum;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumPost;
use CsrDelft\repository\forum\ForumDradenGelezenRepository;
use CsrDelft\repository\forum\ForumDradenReagerenRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\view\bbcode\BbToProsemirror;
use CsrDelft\view\bbcode\ProsemirrorToBb;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ForumPostController extends AbstractController
{
	/**
	 * @var BbToProsemirror
	 */
	private $bbToProsemirror;
	/**
	 * @var ForumPostsRepository
	 */
	private $forumPostsRepository;
	/**
	 * @var ProsemirrorToBb
	 */
	private $prosemirrorToBb;
	/**
	 * @var ForumDradenGelezenRepository
	 */
	private $forumDradenGelezenRepository;
	/**
	 * @var ForumDradenRepository
	 */
	private $forumDradenRepository;
	/**
	 * @var ForumDradenReagerenRepository
	 */
	private $forumDradenReagerenRepository;

	public function __construct(BbToProsemirror              $bbToProsemirror,
															ProsemirrorToBb              $prosemirrorToBb,
															ForumPostsRepository         $forumPostsRepository,
															ForumDradenGelezenRepository $forumDradenGelezenRepository,
															ForumDradenReagerenRepository $forumDradenReagerenRepository,
															ForumDradenRepository        $forumDradenRepository)
	{
		$this->bbToProsemirror = $bbToProsemirror;
		$this->forumPostsRepository = $forumPostsRepository;
		$this->prosemirrorToBb = $prosemirrorToBb;
		$this->forumDradenGelezenRepository = $forumDradenGelezenRepository;
		$this->forumDradenRepository = $forumDradenRepository;
		$this->forumDradenReagerenRepository = $forumDradenReagerenRepository;
	}


	/**
	 * @param ForumPost $post
	 * @Route("/forum/citeren/{post_id}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function citeren(ForumPost $post) {
		if (!$post->magCiteren()) {
			throw $this->createAccessDeniedException("Mag niet citeren");
		}
		return new JsonResponse([
			'van' => $post->uid,
			'naam' => ProfielRepository::getNaam($post->uid, 'user'),
			'content' => $this->bbToProsemirror->toProseMirrorFragment($this->forumPostsRepository->citeerForumPost($post)),
		]);
	}

	/**
	 * @param ForumPost $post
	 * @Route("/forum/tekst/{post_id}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function tekst(ForumPost $post) {
		if (!$post->magBewerken()) {
			throw $this->createAccessDeniedException("Mag niet bewerken");
		}

		return new JsonResponse($this->bbToProsemirror->toProseMirror($post->tekst));
	}

	/**
	 * @param ForumPost $post
	 * @return Response
	 * @Route("/forum/bewerken/{post_id}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function bewerken(ForumPost $post) {
		if (!$post->magBewerken()) {
			throw $this->createAccessDeniedException("Mag niet bewerken");
		}
		$tekst = $this->prosemirrorToBb->convertToBb(json_decode(trim(filter_input(INPUT_POST, 'forumBericht', FILTER_UNSAFE_RAW))));
		$reden = trim(filter_input(INPUT_POST, 'reden', FILTER_UNSAFE_RAW));
		$this->forumPostsRepository->bewerkForumPost($tekst, $reden, $post);
		$this->forumDradenGelezenRepository->setWanneerGelezenDoorLid($post->draad, $post->laatst_gewijzigd);
		return $this->render('forum/partial/post_lijst.html.twig', ['post' => $post]);
	}

	/**
	 * @param ForumPost $post
	 * @return Response
	 * @Route("/forum/verplaatsen/{post_id}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function verplaatsen(ForumPost $post) {
		$oudDraad = $post->draad;
		if (!$oudDraad->magModereren()) {
			throw $this->createAccessDeniedException("Geen moderator");
		}
		$nieuw = filter_input(INPUT_POST, 'draad_id', FILTER_SANITIZE_NUMBER_INT);
		$nieuwDraad = $this->forumDradenRepository->get((int)$nieuw);
		if (!$nieuwDraad->magModereren()) {
			throw $this->createAccessDeniedException("Geen moderator");
		}
		$this->forumPostsRepository->verplaatsForumPost($nieuwDraad, $post);
		$this->forumPostsRepository->goedkeurenForumPost($post);
		return $this->render('forum/partial/post_delete.html.twig', ['post' => $post]);
	}

	/**
	 * @param ForumPost $post
	 * @return Response
	 * @Route("/forum/verwijderen/{post_id}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function verwijderen(ForumPost $post) {
		if (!$post->draad->magModereren()) {
			throw $this->createAccessDeniedException("Geen moderator");
		}
		$this->forumPostsRepository->verwijderForumPost($post);
		return $this->render('forum/partial/post_delete.html.twig', ['post' => $post]);
	}

	/**
	 * @param ForumPost $post
	 * @return Response
	 * @Route("/forum/offtopic/{post_id}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function offtopic(ForumPost $post) {
		if (!$post->draad->magModereren()) {
			throw $this->createAccessDeniedException("Geen moderator");
		}
		$this->forumPostsRepository->offtopicForumPost($post);
		return $this->render('forum/partial/post_lijst.html.twig', ['post' => $post]);
	}

	/**
	 * @param ForumPost $post
	 * @return Response
	 * @Route("/forum/goedkeuren/{post_id}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function goedkeuren(ForumPost $post) {
		if (!$post->draad->magModereren()) {
			throw $this->createAccessDeniedException("Geen moderator");
		}
		$this->forumPostsRepository->goedkeurenForumPost($post);
		return $this->render('forum/partial/post_lijst.html.twig', ['post' => $post]);
	}

	/**
	 * Concept bericht opslaan
	 * @param ForumDeel $deel
	 * @param ForumDraad|null $draad
	 * @return Response
	 * @Route("/forum/concept/{forum_id}/{draad_id}", methods={"POST"}, defaults={"draad_id"=null})
	 * @Auth(P_LOGGED_IN)
	 */
	public function concept(ForumDeel $deel, ForumDraad $draad = null) {
		$titel = trim(filter_input(INPUT_POST, 'titel', FILTER_SANITIZE_STRING));
		$concept = $this->prosemirrorToBb->convertToBb(json_decode(trim(filter_input(INPUT_POST, 'forumBericht', FILTER_UNSAFE_RAW))));
		$ping = filter_input(INPUT_POST, 'ping', FILTER_SANITIZE_STRING);

		// bestaand draadje?
		if ($draad !== null) {
			$draad_id = $draad->draad_id;
			// check draad in forum deel
			if (!$draad || $draad->forum_id !== $deel->forum_id || !$draad->magPosten()) {
				throw $this->createAccessDeniedException("Draad bevindt zich niet in deel");
			}
			if (empty($ping)) {
				$this->forumDradenReagerenRepository->setConcept($deel, $draad_id, $concept);
			} elseif ($ping === 'true') {
				$this->forumDradenReagerenRepository->setWanneerReagerenDoorLid($deel, $draad_id);
			}
			$reageren = $this->forumDradenReagerenRepository->getReagerenVoorDraad($draad);
		} else {
			if (!$deel->magPosten()) {
				throw $this->createAccessDeniedException("Mag niet posten");
			}
			if (empty($ping)) {
				$this->forumDradenReagerenRepository->setConcept($deel, null, $concept, $titel);
			} elseif ($ping === 'true') {
				$this->forumDradenReagerenRepository->setWanneerReagerenDoorLid($deel);
			}
			$reageren = $this->forumDradenReagerenRepository->getReagerenVoorDeel($deel);
		}

		return $this->render('forum/partial/draad_reageren.html.twig', ['reageren' => $reageren]);
	}
}
