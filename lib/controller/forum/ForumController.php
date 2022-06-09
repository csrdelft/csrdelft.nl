<?php

namespace CsrDelft\controller\forum;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumZoeken;
use CsrDelft\repository\forum\ForumCategorieRepository;
use CsrDelft\repository\forum\ForumDelenRepository;
use CsrDelft\repository\forum\ForumDradenGelezenRepository;
use CsrDelft\repository\forum\ForumDradenReagerenRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\service\forum\ForumDelenService;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\BbToProsemirror;
use CsrDelft\view\forum\ForumSnelZoekenForm;
use CsrDelft\view\forum\ForumZoekenForm;
use CsrDelft\view\GenericSuggestiesResponse;
use CsrDelft\view\Icon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van het forum.
 */
class ForumController extends AbstractController
{
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
	/**
	 * @var ForumCategorieRepository
	 */
	private $forumCategorieRepository;
	/**
	 * @var ForumPostsRepository
	 */
	private $forumPostsRepository;
	/**
	 * @var BbToProsemirror
	 */
	private $bbToProsemirror;
	/**
	 * @var ForumDelenService
	 */
	private $forumDelenService;

	public function __construct(
		BbToProsemirror $bbToProsemirror,
		ForumCategorieRepository $forumCategorieRepository,
		ForumDelenService $forumDelenService,
		ForumDradenGelezenRepository $forumDradenGelezenRepository,
		ForumDradenRepository $forumDradenRepository,
		ForumDradenReagerenRepository $forumDradenReagerenRepository,
		ForumPostsRepository $forumPostsRepository
	) {
		$this->forumDradenGelezenRepository = $forumDradenGelezenRepository;
		$this->forumDradenRepository = $forumDradenRepository;
		$this->forumDradenReagerenRepository = $forumDradenReagerenRepository;
		$this->forumCategorieRepository = $forumCategorieRepository;
		$this->forumPostsRepository = $forumPostsRepository;
		$this->bbToProsemirror = $bbToProsemirror;
		$this->forumDelenService = $forumDelenService;
	}

	/**
	 * Overzicht met categorien en forumdelen laten zien.
	 * @Route("/forum", methods={"GET"})
	 * @Auth(P_PUBLIC)
	 */
	public function forum()
	{
		return $this->render('forum/overzicht.html.twig', [
			'zoekform' => new ForumSnelZoekenForm(),
			'categorien' => $this->forumDelenService->getForumIndelingVoorLid(),
		]);
	}

	/**
	 * RSS feed van recente draadjes tonen.
	 * @Route("/forum/rss/csrdelft.xml", methods={"GET"})
	 * @Route("/forum/rss/{private_auth_token}/csrdelft.xml", methods={"GET"})
	 * @Auth(P_PUBLIC)
	 */
	public function rss()
	{
		$response = new Response(null, 200, [
			'Content-Type' => 'application/rss+xml; charset=UTF-8',
		]);
		return $this->render(
			'forum/rss.xml.twig',
			[
				'draden' => $this->forumDelenService->getRecenteForumDraden(
					null,
					null,
					true
				),
				'privatelink' => $this->getUser()
					? $this->getUser()->getRssLink()
					: null,
			],
			$response
		);
	}

	/**
	 * Tonen van alle posts die wachten op goedkeuring.
	 *
	 * @param string|null $query
	 * @param int $pagina
	 * @return Response
	 * @Route("/forum/zoeken/{query}/{pagina<\d+>}", methods={"GET", "POST"}, defaults={"query"=null,"pagina"=1})
	 * @Auth(P_PUBLIC)
	 */
	public function zoeken($query = null, int $pagina = 1)
	{
		$this->forumPostsRepository->setHuidigePagina($pagina, 0);
		$this->forumDradenRepository->setHuidigePagina($pagina, 0);
		$forumZoeken = new ForumZoeken();
		$forumZoeken->zoekterm = $query;
		$zoekform = new ForumZoekenForm($forumZoeken);

		if (!$this->mag(P_LOGGED_IN)) {
			// Reset de waarden waarbinnen een externe gebruiker mag zoeken.
			$override = new ForumZoeken();
			$override->zoekterm = $forumZoeken->zoekterm;
			$forumZoeken = $override;
		}

		return $this->render('forum/resultaten.html.twig', [
			'titel' => 'Zoeken',
			'form' => $zoekform,
			'resultaten' => $this->forumDelenService->zoeken($forumZoeken),
			'query' => $forumZoeken->zoekterm,
		]);
	}

	/**
	 * Draden zoeken op titel voor auto-aanvullen.
	 *
	 * @param Request $request
	 * @param null $zoekterm
	 * @return JsonResponse
	 * @Route("/forum/titelzoeken", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function titelzoeken(Request $request, $zoekterm = null)
	{
		if (!$zoekterm && !$request->query->has('q')) {
			return new JsonResponse([]);
		}

		if (!$zoekterm) {
			$zoekterm = $request->query->get('q');
		}

		$result = [];
		$query = $zoekterm;
		$limit = $request->query->getInt('limit', 5);

		$forumZoeken = ForumZoeken::nieuw($query, $limit, ['titel']);

		$draden = $this->forumDelenService->zoeken($forumZoeken);

		foreach ($draden as $draad) {
			$result[] = $this->draadAutocompleteArray($draad);
		}

		if (empty($result)) {
			$result[] = [
				'url' => '/forum/zoeken/' . urlencode($query),
				'icon' => Icon::getTag('magnifier'),
				'title' => 'Zoeken in forumreacties',
				'label' => 'Zoeken in reacties',
				'value' => htmlspecialchars($query),
			];
		}

		return new JsonResponse($result);
	}

	/**
	 * Shortcut to /recent/1/belangrijk.
	 *
	 * @param int $pagina
	 * @return Response
	 * @Route("/forum/belangrijk/{pagina<\d+>}", methods={"GET"}, defaults={"pagina"=1})
	 * @Auth(P_LOGGED_IN)
	 */
	public function belangrijk(RequestStack $requestStack, int $pagina = 1)
	{
		return $this->recent($requestStack, $pagina, 'belangrijk');
	}

	/**
	 * Recente draadjes laten zien in tabel.
	 *
	 * @param int|string $pagina
	 * @param string|null $belangrijk
	 * @return Response
	 * @Route("/forum/recent/{pagina<\d+>}", methods={"GET"}, defaults={"pagina"=1})
	 * @Route("/forum/recent/{pagina<\d+>}/belangrijk", methods={"GET"}, defaults={"pagina"=1})
	 * @Auth(P_PUBLIC)
	 */
	public function recent(
		RequestStack $requestStack,
		$pagina = 1,
		$belangrijk = null
	) {
		$this->forumDradenRepository->setHuidigePagina((int) $pagina, 0);
		$belangrijk = $belangrijk === 'belangrijk' || $pagina === 'belangrijk';
		$deel = $this->forumDelenService->getRecent($belangrijk);

		$aantalPaginas = $this->forumDradenRepository->getAantalPaginas(
			$deel->forum_id
		);

		if ($pagina > $aantalPaginas) {
			throw $this->createNotFoundException();
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
			'paging' => $aantalPaginas > 1,
			'belangrijk' => $belangrijk ? '/belangrijk' : '',
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
	 * @return GenericSuggestiesResponse
	 * @Route("/forum/categorie/suggestie")
	 * @Auth(P_FORUM_ADMIN)
	 */
	public function forumCategorieSuggestie(Request $request)
	{
		$zoekterm = $request->query->get('q');
		$forumCategories = $this->forumCategorieRepository
			->createQueryBuilder('c')
			->where('c.titel LIKE :zoekterm')
			->setParameter('zoekterm', sql_contains($zoekterm))
			->getQuery()
			->getResult();
		return new GenericSuggestiesResponse($forumCategories);
	}

	/**
	 * Leg bladwijzer
	 *
	 * @param ForumDraad $draad
	 * @Route("/forum/bladwijzer/{draad_id}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function bladwijzer(ForumDraad $draad)
	{
		$timestamp = (int) filter_input(
			INPUT_POST,
			'timestamp',
			FILTER_SANITIZE_NUMBER_INT
		);
		if (
			$this->forumDradenGelezenRepository->setWanneerGelezenDoorLid(
				$draad,
				date_create_immutable('@' . ($timestamp - 1))
			)
		) {
			echo '<img id="timestamp' .
				$timestamp .
				'" src="/plaetjes/famfamfam/tick.png" class="icon" title="Bladwijzer succesvol geplaatst">';
		}
		exit(); //TODO: JsonResponse
	}

	/**
	 * @param ForumDraad $draad
	 * @return array
	 */
	private function draadAutocompleteArray(ForumDraad $draad)
	{
		$url = '/forum/onderwerp/' . $draad->draad_id;

		if (lid_instelling('forum', 'open_draad_op_pagina') == 'ongelezen') {
			$url .= '#ongelezen';
		} elseif (lid_instelling('forum', 'open_draad_op_pagina') == 'laatste') {
			$url .= '#reageren';
		}

		if ($draad->belangrijk) {
			$icon = Icon::getTag($draad->belangrijk);
			$title = 'Dit onderwerp is door het bestuur aangemerkt als belangrijk';
		} elseif ($draad->gesloten) {
			$icon = Icon::getTag('lock');
			$title = 'Dit onderwerp is gesloten, u kunt niet meer reageren';
		} elseif ($draad->plakkerig) {
			$icon = Icon::getTag('note');
			$title = 'Dit onderwerp is plakkerig, het blijft bovenaan';
		} else {
			$icon = Icon::getTag('forum');
			$title = false;
		}

		return [
			'url' => $url,
			'icon' => $icon,
			'title' => $title,
			'label' => $draad->deel->titel,
			'value' => $draad->titel,
		];
	}
}
