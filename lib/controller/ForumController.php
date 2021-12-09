<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\SimpleSpamFilter;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumDraadMeldingNiveau;
use CsrDelft\entity\forum\ForumPost;
use CsrDelft\entity\forum\ForumZoeken;
use CsrDelft\repository\DebugLogRepository;
use CsrDelft\repository\forum\ForumCategorieRepository;
use CsrDelft\repository\forum\ForumDelenMeldingRepository;
use CsrDelft\repository\forum\ForumDelenRepository;
use CsrDelft\repository\forum\ForumDradenGelezenRepository;
use CsrDelft\repository\forum\ForumDradenMeldingRepository;
use CsrDelft\repository\forum\ForumDradenReagerenRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumDradenVerbergenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\BbToProsemirror;
use CsrDelft\view\bbcode\ProsemirrorToBb;
use CsrDelft\view\ChartTimeSeries;
use CsrDelft\view\forum\ForumDeelForm;
use CsrDelft\view\forum\ForumSnelZoekenForm;
use CsrDelft\view\forum\ForumZoekenForm;
use CsrDelft\view\GenericSuggestiesResponse;
use CsrDelft\view\Icon;
use CsrDelft\view\View;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van het forum.
 */
class ForumController extends AbstractController {
	/**
	 * @var DebugLogRepository
	 */
	private $debugLogRepository;
	/**
	 * @var ForumDelenMeldingRepository
	 */
	private $forumDelenMeldingRepository;
	/**
	 * @var ForumDelenRepository
	 */
	private $forumDelenRepository;
	/**
	 * @var ForumDradenGelezenRepository
	 */
	private $forumDradenGelezenRepository;
	/**
	 * @var ForumDradenMeldingRepository
	 */
	private $forumDradenMeldingRepository;
	/**
	 * @var ForumDradenRepository
	 */
	private $forumDradenRepository;
	/**
	 * @var ForumDradenReagerenRepository
	 */
	private $forumDradenReagerenRepository;
	/**
	 * @var ForumDradenVerbergenRepository
	 */
	private $forumDradenVerbergenRepository;
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
	 * @var ProsemirrorToBb
	 */
	private $prosemirrorToBb;

	public function __construct(
		BbToProsemirror $bbToProsemirror,
		ProsemirrorToBb $prosemirrorToBb,
		ForumCategorieRepository $forumCategorieRepository,
		DebugLogRepository $debugLogRepository,
		ForumDradenMeldingRepository $forumDradenMeldingRepository,
		ForumDelenMeldingRepository $forumDelenMeldingRepository,
		ForumDelenRepository $forumDelenRepository,
		ForumDradenGelezenRepository $forumDradenGelezenRepository,
		ForumDradenRepository $forumDradenRepository,
		ForumDradenReagerenRepository $forumDradenReagerenRepository,
		ForumDradenVerbergenRepository $forumDradenVerbergenRepository,
		ForumPostsRepository $forumPostsRepository
	) {
		$this->debugLogRepository = $debugLogRepository;
		$this->forumDradenMeldingRepository = $forumDradenMeldingRepository;
		$this->forumDelenRepository = $forumDelenRepository;
		$this->forumDradenGelezenRepository = $forumDradenGelezenRepository;
		$this->forumDradenRepository = $forumDradenRepository;
		$this->forumDradenReagerenRepository = $forumDradenReagerenRepository;
		$this->forumDradenVerbergenRepository = $forumDradenVerbergenRepository;
		$this->forumCategorieRepository = $forumCategorieRepository;
		$this->forumPostsRepository = $forumPostsRepository;
		$this->forumDelenMeldingRepository = $forumDelenMeldingRepository;
		$this->bbToProsemirror = $bbToProsemirror;
		$this->prosemirrorToBb = $prosemirrorToBb;
	}

	/**
	 * Overzicht met categorien en forumdelen laten zien.
	 * @Route("/forum", methods={"GET"})
	 * @Auth(P_PUBLIC)
	 */
	public function forum() {
		return $this->render('forum/overzicht.html.twig', [
			'zoekform' => new ForumSnelZoekenForm(),
			'categorien' => $this->forumCategorieRepository->getForumIndelingVoorLid(),
		]);
	}

	/**
	 * @param $type
	 * @return ChartTimeSeries
	 * @Route("/forum/grafiekdata/{type}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function grafiekdata($type) {
		$datasets = [];
		if ($type == 'details') {
			foreach ($this->forumDelenRepository->getForumDelenVoorLid() as $deel) {
				$datasets[$deel->titel] = $this->forumPostsRepository->getStatsVoorForumDeel($deel);
			}
		} else {
			$datasets['Totaal'] = $this->forumPostsRepository->getStatsTotal();
		}
		return new ChartTimeSeries($datasets);
	}

	/**
	 * RSS feed van recente draadjes tonen.
	 * @Route("/forum/rss/csrdelft.xml", methods={"GET"})
	 * @Route("/forum/rss/{private_auth_token}/csrdelft.xml", methods={"GET"})
	 * @Auth(P_PUBLIC)
	 */
	public function rss() {
		$response = new Response(null, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
		return $this->render('forum/rss.xml.twig', [
			'draden' => $this->forumDradenRepository->getRecenteForumDraden(null, null, true),
			'privatelink' => $this->getUser() ? $this->getUser()->getRssLink() : null,
		], $response);
	}

	/**
	 * Tonen van alle posts die wachten op goedkeuring.
	 * @Route("/forum/wacht", methods={"GET"})
	 * @Auth(P_FORUM_MOD)
	 */
	public function wacht() {
		return $this->render('forum/wacht.html.twig', [
			'resultaten' => $this->forumDelenRepository->getWachtOpGoedkeuring()
		]);
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
	public function zoeken($query = null, int $pagina = 1) {
		$this->forumPostsRepository->setHuidigePagina($pagina, 0);
		$this->forumDradenRepository->setHuidigePagina($pagina, 0);
		$forumZoeken = new ForumZoeken();
		$forumZoeken->zoekterm = $query;
		$zoekform = new ForumZoekenForm($forumZoeken);

		if (!LoginService::mag(P_LOGGED_IN)) {
			// Reset de waarden waarbinnen een externe gebruiker mag zoeken.
			$override = new ForumZoeken();
			$override->zoekterm = $forumZoeken->zoekterm;
			$forumZoeken = $override;
		}

		return $this->render('forum/resultaten.html.twig', [
			'titel' => 'Zoeken',
			'form' => $zoekform,
			'resultaten' => $this->forumDelenRepository->zoeken($forumZoeken),
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
	public function titelzoeken(Request $request, $zoekterm = null) {
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

		$draden = $this->forumDelenRepository->zoeken($forumZoeken);

		foreach ($draden as $draad) {
			$result[] = $this->draadAutocompleteArray($draad);
		}

		if (empty($result)) {
			$result[] = array(
				'url' => '/forum/zoeken/' . urlencode($query),
				'icon' => Icon::getTag('magnifier'),
				'title' => 'Zoeken in forumreacties',
				'label' => 'Zoeken in reacties',
				'value' => htmlspecialchars($query)
			);
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
	public function belangrijk(int $pagina = 1) {
		return $this->recent($pagina, 'belangrijk');
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
	public function recent(RequestStack $requestStack, $pagina = 1, $belangrijk = null) {
		$this->forumDradenRepository->setHuidigePagina((int)$pagina, 0);
		$belangrijk = $belangrijk === 'belangrijk' || $pagina === 'belangrijk';
		$deel = $this->forumDelenRepository->getRecent($belangrijk);

		$aantalPaginas = $this->forumDradenRepository->getAantalPaginas($deel->forum_id);

		if ($pagina > $aantalPaginas) {
			throw $this->createNotFoundException();
		}

		if (LoginService::isExtern()) {
			$concept = $requestStack->getSession()->remove('forum_bericht');
		} else {
			$concept = $this->forumDradenReagerenRepository->getConcept($draad->deel, $draad->draad_id);
		}
		return $this->render('forum/deel.html.twig', [
			'zoekform' => new ForumSnelZoekenForm(),
			'categorien' => $this->forumCategorieRepository->getForumIndelingVoorLid(),
			'deel' => $deel,
			'paging' => $aantalPaginas > 1,
			'belangrijk' => $belangrijk ? '/belangrijk' : '',
			'post_form_titel' => $this->forumDradenReagerenRepository->getConceptTitel($deel),
			'post_form_tekst' => $this->bbToProsemirror->toProseMirror($concept),
			'reageren' => $this->forumDradenReagerenRepository->getReagerenVoorDeel($deel)
		]);
	}

	/**
	 * Deelforum laten zien met draadjes in tabel.
	 *
	 * @param ForumDeel $deel
	 * @param int|string $pagina or 'laatste' or 'prullenbak'
	 * @return Response
	 * @Route("/forum/deel/{forum_id}/{pagina<\d+>}", methods={"GET","POST"}, defaults={"pagina"=1})
	 * @Auth(P_PUBLIC)
	 */
	public function deel(RequestStack $requestStack, ForumDeel $deel, $pagina = 1) {
		if (!$deel->magLezen()) {
			throw $this->createAccessDeniedException();
		}
		$paging = true;
		if ($pagina === 'laatste') {
			$this->forumDradenRepository->setLaatstePagina($deel->forum_id);
		} elseif ($pagina === 'prullenbak' && $deel->magModereren()) {
			$deel->setForumDraden($this->forumDradenRepository->getPrullenbakVoorDeel($deel));
			$paging = false;
		} elseif ($pagina === 'belangrijk' && $deel->magLezen()) {
			$deel->setForumDraden($this->forumDradenRepository->getBelangrijkeForumDradenVoorDeel($deel));
			$paging = false;
		} else {
			$this->forumDradenRepository->setHuidigePagina((int)$pagina, $deel->forum_id);
		}

		if (LoginService::isExtern()) {
			$concept = $requestStack->getSession()->remove('forum_bericht');
		} else {
			$concept = $this->forumDradenReagerenRepository->getConcept($draad->deel, $draad->draad_id);
		}
		return $this->render('forum/deel.html.twig', [
			'zoekform' => new ForumSnelZoekenForm(),
			'categorien' => $this->forumCategorieRepository->getForumIndelingVoorLid(),
			'deel' => $deel,
			'paging' => $paging && $this->forumDradenRepository->getAantalPaginas($deel->forum_id) > 1,
			'belangrijk' => '',
			'post_form_titel' => $this->forumDradenReagerenRepository->getConceptTitel($deel),
			'post_form_tekst' => $this->bbToProsemirror->toProseMirror($concept),
			'reageren' => $this->forumDradenReagerenRepository->getReagerenVoorDeel($deel),
		]);
	}

	/**
	 * Opzoeken forumdraad van forumpost.
	 *
	 * @param ForumPost $post
	 * @return Response
	 * @Route("/forum/reactie/{post_id}", methods={"GET"})
	 * @Auth(P_PUBLIC)
	 */
	public function reactie(ForumPost $post) {
		if ($post->verwijderd) {
			setMelding('Deze reactie is verwijderd', 0);
		}
		return $this->onderwerp($post->draad, $this->forumPostsRepository->getPaginaVoorPost($post));
	}

	/**
	 * Forumdraadje laten zien met alle zichtbare/verwijderde posts.
	 *
	 * @param ForumDraad $draad
	 * @param int|null $pagina or 'laatste' or 'ongelezen'
	 * @param string|null $statistiek
	 * @return Response
	 * @Route("/forum/onderwerp/{draad_id}/{pagina}/{statistiek}", methods={"GET"}, defaults={"pagina"=null,"statistiek"=null})
	 * @Auth(P_PUBLIC)
	 */
	public function onderwerp(RequestStack $requestStack, ForumDraad $draad, $pagina = null, $statistiek = null) {
		if (!$draad->magLezen()) {
			throw $this->createAccessDeniedException();
		}
		if (LoginService::mag(P_LOGGED_IN)) {
			$gelezen = $draad->getWanneerGelezen();
		} else {
			$gelezen = null;
		}
		if ($pagina === null) {
			$pagina = lid_instelling('forum', 'open_draad_op_pagina');
		}
		$paging = true;
		if ($pagina === 'ongelezen' && $gelezen) {
			$this->forumPostsRepository->setPaginaVoorLaatstGelezen($gelezen);
		} elseif ($pagina === 'laatste') {
			$this->forumPostsRepository->setLaatstePagina($draad->draad_id);
		} elseif ($pagina === 'prullenbak' && $draad->magModereren()) {
			$draad->setForumPosts($this->forumPostsRepository->getPrullenbakVoorDraad($draad));
			$paging = false;
		} else {
			$this->forumPostsRepository->setHuidigePagina((int)$pagina, $draad->draad_id);
		}

		if (LoginService::isExtern()) {
			$concept = $requestStack->getSession()->remove('forum_bericht');
		} else {
			$concept = $this->forumDradenReagerenRepository->getConcept($draad->deel, $draad->draad_id);
		}
		$view = $this->render('forum/draad.html.twig', [
			'zoekform' => new ForumSnelZoekenForm(),
			'draad' => $draad,
			'paging' => $paging && $this->forumPostsRepository->getAantalPaginas($draad->draad_id) > 1,
			'post_form_tekst' => $this->bbToProsemirror->toProseMirror($concept),
			'reageren' => $this->forumDradenReagerenRepository->getReagerenVoorDraad($draad),
			'categorien' => $this->forumCategorieRepository->getForumIndelingVoorLid(),
			'gedeeld_met_opties' => $this->forumDelenRepository->getForumDelenOptiesOmTeDelen($draad->deel),
			'statistiek' => $statistiek === 'statistiek' && $draad->magStatistiekBekijken(),
			'draad_ongelezen' => $gelezen ? $draad->isOngelezen() : true,
			'gelezen_moment' => $gelezen ? $gelezen->datum_tijd : false,
		]);

		if (LoginService::mag(P_LOGGED_IN)) {
			$this->forumDradenGelezenRepository->setWanneerGelezenDoorLid($draad);
		}

		return $view;
	}

	/**
	 * Forum deel aanmaken.
	 * @param Request $request
	 * @return JsonResponse|Response
	 * @Route("/forum/aanmaken", methods={"POST"})
	 * @Auth(P_FORUM_ADMIN)
	 */
	public function aanmaken(Request $request) {
		$deel = $this->forumDelenRepository->nieuwForumDeel();
		$form = $this->createFormulier(ForumDeelForm::class, $deel, [
			'action' => $this->generateUrl('csrdelft_forum_aanmaken'),
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
	 * @Route("/forum/beheren/{forum_id}", methods={"POST"})
	 * @Auth(P_FORUM_ADMIN)
	 */
	public function beheren(Request $request, ForumDeel $deel) {
		$form = $this->createFormulier(ForumDeelForm::class, $deel, [
			'action' => $this->generateUrl('csrdelft_forum_beheren', ['forum_id' => $deel->forum_id]),
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
	 * @return GenericSuggestiesResponse
	 * @Route("/forum/categorie/suggestie")
	 * @Auth(P_FORUM_ADMIN)
	 */
	public function forumCategorieSuggestie(Request $request) {
		$zoekterm = $request->query->get('q');
		$forumCategories = $this->forumCategorieRepository->createQueryBuilder('c')
			->where('c.titel LIKE :zoekterm')
			->setParameter('zoekterm', sql_contains($zoekterm))
			->getQuery()->getResult();
		return new GenericSuggestiesResponse($forumCategories);
	}

	/**
	 * Forum deel verwijderen.
	 *
	 * @param ForumDeel $deel
	 * @return JsonResponse
	 * @Route("/forum/opheffen/{forum_id}", methods={"POST"})
	 * @Auth(P_FORUM_ADMIN)
	 */
	public function opheffen(ForumDeel $deel) {
		$count = count($this->forumDradenRepository->findBy(['forum_id' => $deel->forum_id]));
		if ($count > 0) {
			setMelding('Verwijder eerst alle ' . $count . ' draadjes van dit deelforum uit de database!', -1);
		} else {
			$this->forumDelenRepository->verwijderForumDeel($deel->forum_id);
			setMelding('Deelforum verwijderd', 1);
		}
		return new JsonResponse('/forum'); // redirect
	}

	/**
	 * Forum draad verbergen in zijbalk.
	 *
	 * @param ForumDraad $draad
	 * @return JsonResponse
	 * @Route("/forum/verbergen/{draad_id}", methods={"POST"}))
	 * @Auth(P_LOGGED_IN)
	 */
	public function verbergen(ForumDraad $draad) {
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
	 * @Route("/forum/tonen/{draad_id}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function tonen(ForumDraad $draad) {
		if (!$draad->isVerborgen()) {
			throw new CsrGebruikerException('Onderwerp is niet verborgen');
		}
		$this->forumDradenVerbergenRepository->setVerbergenVoorLid($draad, false);
		return new JsonResponse(true);
	}

	/**
	 * Forum draden die verborgen zijn door lid weer tonen.
	 * @Route("/forum/toonalles", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function toonalles() {
		$aantal = $this->forumDradenVerbergenRepository->getAantalVerborgenVoorLid();
		$this->forumDradenVerbergenRepository->toonAllesVoorLeden([$this->getUid()]);
		setMelding($aantal . ' onderwerp' . ($aantal === 1 ? ' wordt' : 'en worden') . ' weer getoond in de zijbalk', 1);
		return new JsonResponse(true);
	}

	/**
	 * Niveau voor meldingen instellen.
	 *
	 * @param ForumDraad $draad
	 * @param string $niveau
	 *
	 * @return JsonResponse
	 * @Route("/forum/meldingsniveau/{draad_id}/{niveau}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function meldingsniveau(ForumDraad $draad, $niveau) {
		if (!$draad || !$draad->magLezen() || !$draad->magMeldingKrijgen()) {
			throw $this->createAccessDeniedException('Onderwerp mag geen melding voor ontvangen worden');
		}
		if (!ForumDraadMeldingNiveau::isValidValue($niveau)) {
			throw $this->createAccessDeniedException('Ongeldig meldingsniveau gespecificeerd');
		}
		$this->forumDradenMeldingRepository->setNiveauVoorLid($draad, ForumDraadMeldingNiveau::from($niveau));
		return new JsonResponse(true);
	}

	/**
	 * Niveau voor meldingen deelforum instellen
	 *
	 * @param ForumDeel $deel
	 * @param string $niveau
	 *
	 * @return JsonResponse
	 * @Route("/forum/deelmelding/{forum_id}/{niveau}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function deelmelding(ForumDeel $deel, $niveau) {
		if (!$deel || !$deel->magLezen() || !$deel->magMeldingKrijgen()) {
			throw $this->createAccessDeniedException('Deel mag geen melding voor ontvangen worden');
		}
		if ($niveau !== 'aan' && $niveau !== 'uit') {
			throw $this->createAccessDeniedException('Ongeldig meldingsniveau gespecificeerd');
		}
		$this->forumDelenMeldingRepository->setMeldingVoorLid($deel, $niveau === 'aan');
		return new JsonResponse(true);
	}

	/**
	 * Leg bladwijzer
	 *
	 * @param ForumDraad $draad
	 * @Route("/forum/bladwijzer/{draad_id}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function bladwijzer(ForumDraad $draad) {
		$timestamp = (int)filter_input(INPUT_POST, 'timestamp', FILTER_SANITIZE_NUMBER_INT);
		if ($this->forumDradenGelezenRepository->setWanneerGelezenDoorLid($draad, date_create_immutable('@' . ($timestamp - 1)))) {
			echo '<img id="timestamp' . $timestamp . '" src="/plaetjes/famfamfam/tick.png" class="icon" title="Bladwijzer succesvol geplaatst">';
		}
		exit; //TODO: JsonResponse
	}

	/**
	 * Wijzig een eigenschap van een draadje.
	 *
	 * @param ForumDraad $draad
	 * @param string $property
	 * @return Response
	 * @Route("/forum/wijzigen/{draad_id}/{property}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function wijzigen(ForumDraad $draad, $property) {
		// gedeelde moderators mogen dit niet
		if (!$draad->deel->magModereren()) {
			throw $this->createAccessDeniedException();
		}
		if (in_array($property, array('verwijderd', 'gesloten', 'plakkerig', 'eerste_post_plakkerig', 'pagina_per_post'))) {
			$value = !$draad->$property;
			if ($property === 'belangrijk' && !LoginService::mag(P_FORUM_BELANGRIJK)) {
				throw $this->createAccessDeniedException();
			}
		} elseif ($property === 'forum_id' || $property === 'gedeeld_met') {
			$value = (int)filter_input(INPUT_POST, $property, FILTER_SANITIZE_NUMBER_INT);
			if ($property === 'forum_id') {
				$deel = $this->forumDelenRepository->get($value);
				if (!$deel->magModereren()) {
					throw $this->createAccessDeniedException();
				}
			} elseif ($value === 0) {
				$value = null;
			}
		} elseif ($property === 'titel' || $property === 'belangrijk') {
			$value = trim(filter_input(INPUT_POST, $property, FILTER_SANITIZE_STRING));
			if (empty($value)) {
				$value = null;
			}
		} else {
			throw $this->createAccessDeniedException("Kan draad niet wijzigen");
		}
		$this->forumDradenRepository->wijzigForumDraad($draad, $property, $value);
		if (is_bool($value)) {
			$wijziging = ($value ? 'wel ' : 'niet ') . $property;
		} else {
			$wijziging = $property . ' = ' . $value;
		}
		setMelding('Wijziging geslaagd: ' . $wijziging, 1);
		if ($property === 'belangrijk' || $property === 'forum_id' || $property === 'titel' || $property === 'gedeeld_met') {
			return $this->redirectToRoute('csrdelft_forum_onderwerp', ['draad_id' => $draad->draad_id]);
		} else {
			return new JsonResponse(true);
		}
	}

	/**
	 * Forum post toevoegen en evt. nieuw draadje aanmaken.
	 * @TODO refactor deze veel te ingewikkelde functie en splits in meerdere functies, bijvoorbeeld in het ForumPostsModel
	 *
	 * @param ForumDeel $deel
	 * @param ForumDraad|null $draad
	 * @return RedirectResponse
	 * @Route("/forum/posten/{forum_id}/{draad_id}", methods={"POST"}, defaults={"draad_id"=null})
	 * @Auth(P_PUBLIC)
	 */
	public function posten(RequestStack $requestStack, ForumDeel $deel, ForumDraad $draad = null) {
		// post in bestaand draadje?
		$titel = null;
		if ($draad !== null) {
			// check draad in forum deel
			if (!$draad || $draad->forum_id !== $deel->forum_id || !$draad->magPosten()) {
				throw $this->createAccessDeniedException('Draad bestaat niet');
			}
			$redirect = $this->redirectToRoute('csrdelft_forum_onderwerp', ['draad_id' => $draad->draad_id]);
			$nieuw = false;
		} else {
			if (!$deel->magPosten()) {
				throw $this->createAccessDeniedException('Mag niet posten');
			}
			$redirect = $this->redirectToRoute('csrdelft_forum_deel', ['forum_id' => $deel->forum_id]);
			$nieuw = true;

			$titel = trim(filter_input(INPUT_POST, 'titel', FILTER_SANITIZE_STRING));
		}
		$tekst = $this->prosemirrorToBb->convertToBb(json_decode(trim(filter_input(INPUT_POST, 'forumBericht', FILTER_UNSAFE_RAW))));

		if (empty($tekst)) {
			setMelding('Bericht mag niet leeg zijn', -1);
			return $redirect;
		}

		// voorkom dubbelposts
		if (isset($_SESSION['forum_laatste_post_tekst']) && $_SESSION['forum_laatste_post_tekst'] === $tekst) {
			setMelding('Uw reactie is al geplaatst', 0);

			// concept wissen
			if ($nieuw) {
				$this->forumDradenReagerenRepository->setConcept($deel);
			} else {
				$this->forumDradenReagerenRepository->setConcept($deel, $draad->draad_id);
			}

			return $redirect;
		}

		if (LoginService::mag(P_LOGGED_IN)) {
			// concept opslaan
			if ($draad == null) {
				$this->forumDradenReagerenRepository->setConcept($deel, null, $tekst, $titel);
			} else {
				$this->forumDradenReagerenRepository->setConcept($deel, $draad->draad_id, $tekst);
			}
		}


		// externen checks
		$mailadres = null;
		$wacht_goedkeuring = false;
		if (!LoginService::mag(P_LOGGED_IN)) {
			$filter = new SimpleSpamfilter();
			$spamtrap = filter_input(INPUT_POST, 'firstname', FILTER_UNSAFE_RAW);

			if (!empty($spamtrap) || ($tekst && $filter->isSpam($tekst)) || (isset($titel) && $titel && $filter->isSpam($titel))) {
				$this->debugLogRepository->log(static::class, 'posten', [$deel->forum_id, $draad->draad_id], 'SPAM ' . $tekst);
				setMelding('SPAM', -1);
				throw $this->createAccessDeniedException("");
			}

			$wacht_goedkeuring = true;
			$mailadres = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
			if (!email_like($mailadres)) {
				setMelding('U moet een geldig e-mailadres opgeven!', -1);
				$requestStack->getSession()->set('forum_bericht', $tekst);
				return $redirect;
			}
			if ($filter->isSpam($mailadres)) { //TODO: logging
				setMelding('SPAM', -1);
				throw $this->createAccessDeniedException('SPAM');
			}
		}

		// post in nieuw draadje?
		if ($nieuw) {
			if (empty($titel)) {
				setMelding('U moet een titel opgeven!', -1);
				return $redirect;
			}
			// maak draad
			$draad = $this->forumDradenRepository->maakForumDraad($deel, $titel, $wacht_goedkeuring);
		}

		// maak post
		$post = $this->forumPostsRepository->maakForumPost($draad, $tekst, $_SERVER['REMOTE_ADDR'], $wacht_goedkeuring, $mailadres);

		// bericht sturen naar pubcie@csrdelft dat er een bericht op goedkeuring wacht?
		if ($wacht_goedkeuring) {
			setMelding('Uw bericht is opgeslagen en zal als het goedgekeurd is geplaatst worden.', 1);

			$url = $this->generateUrl('csrdelft_forum_onderwerp', ['draad_id' => $draad->draad_id, '_fragment' => $post->post_id]);
			mail('pubcie@csrdelft.nl', 'Nieuw bericht wacht op goedkeuring', $url . "\n\nDe inhoud van het bericht is als volgt: \n\n" . str_replace('\r\n', "\n", $tekst) . "\n\nEINDE BERICHT", "From: pubcie@csrdelft.nl\r\nReply-To: " . $mailadres);
		} else {

			// direct goedkeuren voor ingelogd
			$this->forumPostsRepository->goedkeurenForumPost($post);
			$this->forumDradenMeldingRepository->stuurMeldingen($post);
			if ($nieuw) {
				$this->forumDelenMeldingRepository->stuurMeldingen($post);
			}
			setMelding(($nieuw ? 'Draad' : 'Post') . ' succesvol toegevoegd', 1);
			if ($nieuw && lid_instelling('forum', 'meldingEigenDraad') === 'ja') {
				$this->forumDradenMeldingRepository->setNiveauVoorLid($draad, ForumDraadMeldingNiveau::ALTIJD());
			}

			$redirect = $this->redirectToRoute('csrdelft_forum_reactie', ['post_id' => $post->post_id, '_fragment' => $post->post_id]);
		}

		// concept wissen
		if ($nieuw) {
			$this->forumDradenReagerenRepository->setConcept($deel);
		} else {
			$this->forumDradenReagerenRepository->setConcept($deel, $draad->draad_id);
		}

		// markeer als gelezen
		if (LoginService::mag(P_LOGGED_IN)) {
			$this->forumDradenGelezenRepository->setWanneerGelezenDoorLid($draad, $post->laatst_gewijzigd);
		}

		// voorkom dubbelposts
		$_SESSION['forum_laatste_post_tekst'] = $tekst;

		// redirect naar post
		return $redirect;
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

	/**
	 * @param ForumDraad $draad
	 * @return array
	 */
	private function draadAutocompleteArray(ForumDraad $draad) {
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
			'value' => $draad->titel
		];
	}
}
