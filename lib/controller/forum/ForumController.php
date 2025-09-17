<?php

namespace CsrDelft\controller\forum;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\common\Util\SqlUtil;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumZoeken;
use CsrDelft\repository\forum\ForumCategorieRepository;
use CsrDelft\repository\forum\ForumDradenGelezenRepository;
use CsrDelft\repository\forum\ForumDradenReagerenRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\service\forum\ForumDelenService;
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
	public function __construct(
		private readonly BbToProsemirror $bbToProsemirror,
		private readonly ForumCategorieRepository $forumCategorieRepository,
		private readonly ForumDelenService $forumDelenService,
		private readonly ForumDradenGelezenRepository $forumDradenGelezenRepository,
		private readonly ForumDradenRepository $forumDradenRepository,
		private readonly ForumDradenReagerenRepository $forumDradenReagerenRepository,
		private readonly ForumPostsRepository $forumPostsRepository
	) {
	}

	/**
	 * Overzicht met categorien en forumdelen laten zien.
	 * @Auth(P_PUBLIC)
	 */
	#[Route(path: '/forum', methods: ['GET'])]
	public function forum()
	{
		return $this->render('forum/overzicht.html.twig', [
			'zoekform' => new ForumSnelZoekenForm(),
			'categorien' => $this->forumDelenService->getForumIndelingVoorLid(),
		]);
	}

	/**
	 * RSS feed van recente draadjes tonen.
	 * @Auth(P_PUBLIC)
	 */
	#[Route(path: '/forum/rss/csrdelft.xml', methods: ['GET'])]
	#[
		Route(
			path: '/forum/rss/{private_auth_token}/csrdelft.xml',
			methods: ['GET']
		)
	]
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
	 * @Auth(P_PUBLIC)
	 */
	#[
		Route(
			path: '/forum/zoeken/{query}/{pagina<\d+>}',
			methods: ['GET', 'POST'],
			defaults: ['query' => null, 'pagina' => 1]
		)
	]
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
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/forum/titelzoeken', methods: ['GET'])]
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
	 * @Auth(P_LOGGED_IN)
	 */
	#[
		Route(
			path: '/forum/belangrijk/{pagina<\d+>}',
			methods: ['GET'],
			defaults: ['pagina' => 1]
		)
	]
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
	 * @Auth(P_PUBLIC)
	 */
	#[
		Route(
			path: '/forum/recent/{pagina<\d+>}',
			methods: ['GET'],
			defaults: ['pagina' => 1]
		)
	]
	#[
		Route(
			path: '/forum/recent/{pagina<\d+>}/belangrijk',
			methods: ['GET'],
			defaults: ['pagina' => 1]
		)
	]
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
	 * @Auth(P_FORUM_ADMIN)
	 */
	#[Route(path: '/forum/categorie/suggestie')]
	public function forumCategorieSuggestie(Request $request)
	{
		$zoekterm = $request->query->get('q');
		$forumCategories = $this->forumCategorieRepository
			->createQueryBuilder('c')
			->where('c.titel LIKE :zoekterm')
			->setParameter('zoekterm', SqlUtil::sql_contains($zoekterm))
			->getQuery()
			->getResult();
		return new GenericSuggestiesResponse($forumCategories);
	}

	/**
	 * Leg bladwijzer
	 *
	 * @param ForumDraad $draad
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/forum/bladwijzer/{draad_id}', methods: ['POST'])]
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
				'" src="/plaetjes/famfamfam/tick.png" class="icon" title="Succesvol gemarkeerd als ongelezen">';
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

		if (
			InstellingUtil::lid_instelling('forum', 'open_draad_op_pagina') ==
			'ongelezen'
		) {
			$url .= '#ongelezen';
		} elseif (
			InstellingUtil::lid_instelling('forum', 'open_draad_op_pagina') ==
			'laatste'
		) {
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
