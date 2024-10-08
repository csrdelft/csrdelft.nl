<?php

namespace CsrDelft\controller\forum;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\FlashType;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\repository\forum\ForumDelenRepository;
use CsrDelft\repository\forum\ForumDradenReagerenRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\service\forum\ForumDelenService;
use CsrDelft\view\bbcode\BbToProsemirror;
use CsrDelft\view\ChartTimeSeries;
use CsrDelft\view\forum\ForumDeelForm;
use CsrDelft\view\forum\ForumSnelZoekenForm;
use CsrDelft\view\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ForumDeelController extends AbstractController
{
	public function __construct(
		private readonly ForumDradenRepository $forumDradenRepository,
		private readonly ForumDelenService $forumDelenService,
		private readonly ForumDradenReagerenRepository $forumDradenReagerenRepository,
		private readonly ForumDelenRepository $forumDelenRepository,
		private readonly ForumPostsRepository $forumPostsRepository,
		private readonly BbToProsemirror $bbToProsemirror
	) {
	}

	/**
	 * Deelforum laten zien met draadjes in tabel.
	 *
	 * @param ForumDeel $deel
	 * @param int|string $pagina or 'laatste' or 'prullenbak'
	 * @return Response
	 * @Auth(P_PUBLIC)
	 */
	#[
		Route(
			path: '/forum/deel/{forum_id}/{pagina<\d+>}',
			methods: ['GET', 'POST'],
			defaults: ['pagina' => 1]
		)
	]
	public function deel(RequestStack $requestStack, ForumDeel $deel, $pagina = 1)
	{
		if (!$deel->magLezen()) {
			throw $this->createAccessDeniedException();
		}
		$paging = true;
		if ($pagina === 'laatste') {
			$this->forumDradenRepository->setLaatstePagina($deel->forum_id);
		} elseif ($pagina === 'prullenbak' && $deel->magModereren()) {
			$deel->setForumDraden(
				$this->forumDradenRepository->getPrullenbakVoorDeel($deel)
			);
			$paging = false;
		} elseif ($pagina === 'belangrijk' && $deel->magLezen()) {
			$deel->setForumDraden(
				$this->forumDradenRepository->getBelangrijkeForumDradenVoorDeel($deel)
			);
			$paging = false;
		} else {
			$this->forumDradenRepository->setHuidigePagina(
				(int) $pagina,
				$deel->forum_id
			);
		}

		if ($this->getUser()) {
			$concept = $this->forumDradenReagerenRepository->getConcept($deel);
		} else {
			$concept = $requestStack->getSession()->remove('forum_bericht');
		}
		return $this->render('forum/deel.html.twig', [
			'zoekform' => new ForumSnelZoekenForm(),
			'categorien' => $this->forumDelenService->getForumIndelingVoorLid(),
			'deel' => $deel,
			'paging' =>
				$paging &&
				$this->forumDradenRepository->getAantalPaginas($deel->forum_id) > 1,
			'belangrijk' => '',
			'post_form_titel' => $this->forumDradenReagerenRepository->getConceptTitel(
				$deel
			),
			'post_form_tekst' => $this->bbToProsemirror->toProseMirror($concept),
			'reageren' => $this->forumDradenReagerenRepository->getReagerenVoorDeel(
				$deel
			),
		]);
	}

	/**
	 * Forum deel aanmaken.
	 * @param Request $request
	 * @return JsonResponse|Response
	 * @Auth(P_FORUM_ADMIN)
	 */
	#[Route(path: '/forum/aanmaken', methods: ['POST'])]
	public function aanmaken(Request $request)
	{
		$deel = $this->forumDelenRepository->nieuwForumDeel();
		$form = $this->createFormulier(ForumDeelForm::class, $deel, [
			'action' => $this->generateUrl('csrdelft_forum_forumdeel_aanmaken'),
			'aanmaken' => true,
		]);

		$form->handleRequest($request);

		if ($form->isPosted() && $form->validate()) {
			$this->forumDelenRepository->create($deel);
			return new JsonResponse(true);
		} else {
			return new Response($form->createModalView());
		}
	}

	/**
	 * Forum deel bewerken.
	 *
	 * @param Request $request
	 * @param ForumDeel $deel
	 * @return View|Response
	 * @Auth(P_FORUM_ADMIN)
	 */
	#[Route(path: '/forum/beheren/{forum_id}', methods: ['POST'])]
	public function beheren(Request $request, ForumDeel $deel)
	{
		$form = $this->createFormulier(ForumDeelForm::class, $deel, [
			'action' => $this->generateUrl('csrdelft_forum_forumdeel_beheren', [
				'forum_id' => $deel->forum_id,
			]),
			'aanmaken' => false,
		]);

		$form->handleRequest($request);
		if ($form->isPosted() && $form->validate()) {
			$this->forumDelenRepository->update($deel);
			return new JsonResponse(true);
		} else {
			return new Response($form->createModalView());
		}
	}

	/**
	 * Forum deel verwijderen.
	 *
	 * @param ForumDeel $deel
	 * @return JsonResponse
	 * @Auth(P_FORUM_ADMIN)
	 */
	#[Route(path: '/forum/opheffen/{forum_id}', methods: ['POST'])]
	public function opheffen(ForumDeel $deel)
	{
		$count = count(
			$this->forumDradenRepository->findBy(['forum_id' => $deel->forum_id])
		);
		if ($count > 0) {
			$this->addFlash(
				FlashType::ERROR,
				'Verwijder eerst alle ' .
					$count .
					' draadjes van dit deelforum uit de database!'
			);
		} else {
			$this->forumDelenService->verwijderForumDeel($deel->forum_id);
			$this->addFlash(FlashType::SUCCESS, 'Deelforum verwijderd');
		}
		return new JsonResponse('/forum'); // redirect
	}

	/**
	 * @param $type
	 * @return ChartTimeSeries
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/forum/grafiekdata/{type}', methods: ['POST'])]
	public function grafiekdata($type)
	{
		$datasets = [];
		if ($type == 'details') {
			foreach ($this->forumDelenRepository->getForumDelenVoorLid() as $deel) {
				$datasets[
					$deel->titel
				] = $this->forumPostsRepository->getStatsVoorForumDeel($deel);
			}
		} else {
			$datasets['Totaal'] = $this->forumPostsRepository->getStatsTotal();
		}
		return new ChartTimeSeries($datasets);
	}

	/**
	 * Tonen van alle posts die wachten op goedkeuring.
	 * @Auth(P_FORUM_MOD)
	 */
	#[Route(path: '/forum/wacht', methods: ['GET'])]
	public function wacht()
	{
		return $this->render('forum/wacht.html.twig', [
			'resultaten' => $this->forumDelenService->getWachtOpGoedkeuring(),
		]);
	}
}
