<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\common\SimpleSpamFilter;
use CsrDelft\model\DebugLogModel;
use CsrDelft\model\entity\forum\ForumDraad;
use CsrDelft\model\entity\forum\ForumDraadMeldingNiveau;
use CsrDelft\model\entity\forum\ForumZoeken;
use CsrDelft\model\entity\security\Account;
use CsrDelft\model\forum\ForumDelenModel;
use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\forum\ForumModel;
use CsrDelft\model\forum\ForumPostsModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\forum\ForumDelenMeldingRepository;
use CsrDelft\repository\forum\ForumDradenGelezenRepository;
use CsrDelft\repository\forum\ForumDradenMeldingRepository;
use CsrDelft\repository\forum\ForumDradenReagerenRepository;
use CsrDelft\repository\forum\ForumDradenVerbergenRepository;
use CsrDelft\view\ChartTimeSeries;
use CsrDelft\view\forum\ForumDeelForm;
use CsrDelft\view\forum\ForumSnelZoekenForm;
use CsrDelft\view\forum\ForumZoekenForm;
use CsrDelft\view\Icon;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;


/**
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van het forum.
 */
class ForumController extends AbstractController {
	/**
	 * @var DebugLogModel
	 */
	private $debugLogModel;
	/**
	 * @var ForumDelenMeldingRepository
	 */
	private $forumDelenMeldingRepository;
	/**
	 * @var ForumDelenModel
	 */
	private $forumDelenModel;
	/**
	 * @var ForumDradenGelezenRepository
	 */
	private $forumDradenGelezenRepository;
	/**
	 * @var ForumDradenMeldingRepository
	 */
	private $forumDradenMeldingRepository;
	/**
	 * @var ForumDradenModel
	 */
	private $forumDradenModel;
	/**
	 * @var ForumDradenReagerenRepository
	 */
	private $forumDradenReagerenRepository;
	/**
	 * @var ForumDradenVerbergenRepository
	 */
	private $forumDradenVerbergenRepository;
	/**
	 * @var ForumModel
	 */
	private $forumModel;
	/**
	 * @var ForumPostsModel
	 */
	private $forumPostsModel;

	public function __construct(
		ForumModel $forumModel,
		DebugLogModel $debugLogModel,
		ForumDradenMeldingRepository $forumDradenMeldingRepository,
		ForumDelenMeldingRepository $forumDelenMeldingRepository,
		ForumDelenModel $forumDelenModel,
		ForumDradenGelezenRepository $forumDradenGelezenRepository,
		ForumDradenModel $forumDradenModel,
		ForumDradenReagerenRepository $forumDradenReagerenRepository,
		ForumDradenVerbergenRepository $forumDradenVerbergenRepository,
		ForumPostsModel $forumPostsModel
	) {
		$this->debugLogModel = $debugLogModel;
		$this->forumDradenMeldingRepository = $forumDradenMeldingRepository;
		$this->forumDelenModel = $forumDelenModel;
		$this->forumDradenGelezenRepository = $forumDradenGelezenRepository;
		$this->forumDradenModel = $forumDradenModel;
		$this->forumDradenReagerenRepository = $forumDradenReagerenRepository;
		$this->forumDradenVerbergenRepository = $forumDradenVerbergenRepository;
		$this->forumModel = $forumModel;
		$this->forumPostsModel = $forumPostsModel;
		$this->forumDelenMeldingRepository = $forumDelenMeldingRepository;
	}

	/**
	 * Overzicht met categorien en forumdelen laten zien.
	 */
	public function forum() {
		return view('forum.overzicht', [
			'zoekform' => new ForumSnelZoekenForm(),
			'categorien' => $this->forumModel->getForumIndelingVoorLid()
		]);
	}

	public function grafiekdata($type) {
		$datasets = [];
		if ($type == 'details') {
			foreach ($this->forumDelenModel->getForumDelenVoorLid() as $deel) {
				$datasets[$deel->titel] = $this->forumPostsModel->getStatsVoorForumDeel($deel);
			}
		} else {
			$datasets['Totaal'] = $this->forumPostsModel->getStatsTotal();
		}
		return new ChartTimeSeries($datasets);
	}

	/**
	 * RSS feed van recente draadjes tonen.
	 */
	public function rss() {
		header('Content-Type: application/rss+xml; charset=UTF-8');
		/**
		 * @var Account $account
		 */
		return view('forum.rss', [
			'draden' => $this->forumDradenModel->getRecenteForumDraden(null, null, true),
			'privatelink' => LoginModel::getAccount()->getRssLink()
		]);
	}

	/**
	 * Tonen van alle posts die wachten op goedkeuring.
	 */
	public function wacht() {
		return view('forum.wacht', [
			'resultaten' => $this->forumDelenModel->getWachtOpGoedkeuring()
		]);
	}

	/**
	 * Tonen van alle posts die wachten op goedkeuring.
	 *
	 * @param string $query
	 * @param int $pagina
	 * @return View
	 */
	public function zoeken($query = null, int $pagina = 1) {
		$this->forumPostsModel->setHuidigePagina($pagina, 0);
		$this->forumDradenModel->setHuidigePagina($pagina, 0);
		$forumZoeken = new ForumZoeken();
		$forumZoeken->zoekterm = $query;
		$zoekform = new ForumZoekenForm($forumZoeken);

		if (!LoginModel::mag(P_LOGGED_IN)) {
			// Reset de waarden waarbinnen een externe gebruiker mag zoeken.
			$override = new ForumZoeken();
			$override->zoekterm = $forumZoeken->zoekterm;
			$forumZoeken = $override;
		}

		return view('forum.resultaten', [
			'titel' => 'Zoeken',
			'form' => $zoekform,
			'resultaten' => $this->forumDelenModel->zoeken($forumZoeken),
			'query' => $forumZoeken->zoekterm,
		]);
	}

	/**
	 * Draden zoeken op titel voor auto-aanvullen.
	 *
	 * @param Request $request
	 * @param null $zoekterm
	 * @return View
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

		$draden = $this->forumDelenModel->zoeken($forumZoeken);

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
	 * @return View
	 */
	public function belangrijk($pagina = 1) {
		return $this->recent($pagina, 'belangrijk');
	}

	/**
	 * Recente draadjes laten zien in tabel.
	 *
	 * @param int|string $pagina
	 * @param string|null $belangrijk
	 * @return View
	 */
	public function recent($pagina = 1, $belangrijk = null) {
		$this->forumDradenModel->setHuidigePagina((int)$pagina, 0);
		$belangrijk = $belangrijk === 'belangrijk' || $pagina === 'belangrijk';
		$deel = $this->forumDelenModel->getRecent($belangrijk);

		return view('forum.deel', [
			'zoekform' => new ForumSnelZoekenForm(),
			'categorien' => $this->forumModel->getForumIndelingVoorLid(),
			'deel' => $deel,
			'paging' => $this->forumDradenModel->getAantalPaginas($deel->forum_id) > 1,
			'belangrijk' => $belangrijk ? '/belangrijk' : '',
			'post_form_titel' => $this->forumDradenReagerenRepository->getConceptTitel($deel),
			'post_form_tekst' => $this->forumDradenReagerenRepository->getConcept($deel),
			'reageren' => $this->forumDradenReagerenRepository->getReagerenVoorDeel($deel)
		]);
	}

	/**
	 * Deelforum laten zien met draadjes in tabel.
	 *
	 * @param int $forum_id
	 * @param int|string $pagina or 'laatste' or 'prullenbak'
	 * @return View
	 * @throws CsrGebruikerException
	 */
	public function deel(int $forum_id, $pagina = 1) {
		$deel = $this->forumDelenModel->get($forum_id);
		if (!$deel->magLezen()) {
			throw new CsrToegangException();
		}
		$paging = true;
		if ($pagina === 'laatste') {
			$this->forumDradenModel->setLaatstePagina($deel->forum_id);
		} elseif ($pagina === 'prullenbak' && $deel->magModereren()) {
			$deel->setForumDraden($this->forumDradenModel->getPrullenbakVoorDeel($deel));
			$paging = false;
		} elseif ($pagina === 'belangrijk' && $deel->magLezen()) {
			$deel->setForumDraden($this->forumDradenModel->getBelangrijkeForumDradenVoorDeel($deel));
			$paging = false;
		} else {
			$this->forumDradenModel->setHuidigePagina((int)$pagina, $deel->forum_id);
		}
		return view('forum.deel', [
			'zoekform' => new ForumSnelZoekenForm(),
			'categorien' => $this->forumModel->getForumIndelingVoorLid(),
			'deel' => $deel,
			'paging' => $paging && $this->forumDradenModel->getAantalPaginas($deel->forum_id) > 1,
			'belangrijk' => '',
			'post_form_titel' => $this->forumDradenReagerenRepository->getConceptTitel($deel),
			'post_form_tekst' => $this->forumDradenReagerenRepository->getConcept($deel),
			'reageren' => $this->forumDradenReagerenRepository->getReagerenVoorDeel($deel),
			'deelmelding' => $this->forumDelenMeldingRepository->lidWilMeldingVoorDeel($deel)
		]);
	}

	/**
	 * Opzoeken forumdraad van forumpost.
	 *
	 * @param int $post_id
	 * @return View
	 * @throws CsrGebruikerException
	 */
	public function reactie(int $post_id) {
		$post = $this->forumPostsModel->get($post_id);
		if ($post->verwijderd) {
			setMelding('Deze reactie is verwijderd', 0);
		}
		return $this->onderwerp($post->draad_id, $this->forumPostsModel->getPaginaVoorPost($post));
	}

	/**
	 * Forumdraadje laten zien met alle zichtbare/verwijderde posts.
	 *
	 * @param int $draad_id
	 * @param int $pagina or 'laatste' or 'ongelezen'
	 * @param string|null $statistiek
	 * @return View
	 * @throws CsrGebruikerException
	 */
	public function onderwerp(int $draad_id, $pagina = null, $statistiek = null) {
		$draad = $this->forumDradenModel->get($draad_id);
		if (!$draad->magLezen()) {
			throw new CsrToegangException();
		}
		if (LoginModel::mag(P_LOGGED_IN)) {
			$gelezen = $draad->getWanneerGelezen();
		} else {
			$gelezen = false;
		}
		if ($pagina === null) {
			$pagina = lid_instelling('forum', 'open_draad_op_pagina');
		}
		$paging = true;
		if ($pagina === 'ongelezen' && $gelezen) {
			$this->forumPostsModel->setPaginaVoorLaatstGelezen($gelezen);
		} elseif ($pagina === 'laatste') {
			$this->forumPostsModel->setLaatstePagina($draad->draad_id);
		} elseif ($pagina === 'prullenbak' && $draad->magModereren()) {
			$draad->setForumPosts($this->forumPostsModel->getPrullenbakVoorDraad($draad));
			$paging = false;
		} else {
			$this->forumPostsModel->setHuidigePagina((int)$pagina, $draad->draad_id);
		}

		$view = view('forum.draad', [
			'zoekform' => new ForumSnelZoekenForm(),
			'draad' => $draad,
			'paging' => $paging && $this->forumPostsModel->getAantalPaginas($draad->draad_id) > 1,
			'post_form_tekst' => $this->forumDradenReagerenRepository->getConcept($draad->getForumDeel(), $draad->draad_id),
			'reageren' => $this->forumDradenReagerenRepository->getReagerenVoorDraad($draad),
			'categorien' => $this->forumModel->getForumIndelingVoorLid(),
			'gedeeld_met_opties' => $this->forumDelenModel->getForumDelenOptiesOmTeDelen($draad->getForumDeel()),
			'statistiek' => $statistiek === 'statistiek' && $draad->magStatistiekBekijken(),
			'draad_ongelezen' => $gelezen ? $draad->isOngelezen() : true,
			'gelezen_moment' => $gelezen ? $gelezen->datum_tijd->getTimestamp() : false,
			'meldingsniveau' => $draad->magMeldingKrijgen() ? $this->forumDradenMeldingRepository->getVoorkeursNiveauVoorLid($draad) : '',
		]);

		if (LoginModel::mag(P_LOGGED_IN)) {
			$this->forumDradenGelezenRepository->setWanneerGelezenDoorLid($draad);
		}

		return $view;
	}

	/**
	 * Forum deel aanmaken.
	 * @return View
	 * @throws CsrGebruikerException
	 */
	public function aanmaken() {
		$deel = $this->forumDelenModel->nieuwForumDeel();
		$form = new ForumDeelForm($deel, true); // fetches POST values itself
		if ($form->validate()) {
			$this->forumDelenModel->create($deel);
			return new JsonResponse(true);
		} else {
			return $form;
		}
	}

	/**
	 * Forum deel bewerken.
	 *
	 * @param int $forum_id
	 *
	 * @return View
	 * @throws CsrGebruikerException
	 */
	public function beheren(int $forum_id) {
		$deel = $this->forumDelenModel->get($forum_id);
		$form = new ForumDeelForm($deel); // fetches POST values itself
		if ($form->validate()) {
			$rowCount = $this->forumDelenModel->update($deel);
			if ($rowCount !== 1) {
				throw new CsrGebruikerException('Forum beheren mislukt!');
			}
			return new JsonResponse(true);
		} else {
			return $form;
		}
	}

	/**
	 * Forum deel verwijderen.
	 *
	 * @param int $forum_id
	 * @return View
	 * @throws CsrGebruikerException
	 * @throws CsrException
	 */
	public function opheffen(int $forum_id) {
		$deel = $this->forumDelenModel->get($forum_id);
		$count = $this->forumDradenModel->count('forum_id = ?', array($deel->forum_id));
		if ($count > 0) {
			setMelding('Verwijder eerst alle ' . $count . ' draadjes van dit deelforum uit de database!', -1);
		} else {
			$this->forumDelenModel->verwijderForumDeel($deel->forum_id);
			setMelding('Deelforum verwijderd', 1);
		}
		return new JsonResponse('/forum'); // redirect
	}

	/**
	 * Forum draad verbergen in zijbalk.
	 *
	 * @param int $draad_id
	 *
	 * @return View
	 * @throws CsrGebruikerException
	 * @throws CsrException
	 */
	public function verbergen(int $draad_id) {
		$draad = $this->forumDradenModel->get($draad_id);
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
	 * @param int $draad_id
	 *
	 * @return View
	 * @throws CsrGebruikerException
	 * @throws CsrException
	 */
	public function tonen(int $draad_id) {
		$draad = $this->forumDradenModel->get($draad_id);
		if (!$draad->isVerborgen()) {
			throw new CsrGebruikerException('Onderwerp is niet verborgen');
		}
		$this->forumDradenVerbergenRepository->setVerbergenVoorLid($draad, false);
		return new JsonResponse(true);
	}

	/**
	 * Forum draden die verborgen zijn door lid weer tonen.
	 */
	public function toonalles() {
		$aantal = $this->forumDradenVerbergenRepository->getAantalVerborgenVoorLid();
		$this->forumDradenVerbergenRepository->toonAllesVoorLid(LoginModel::getUid());
		setMelding($aantal . ' onderwerp' . ($aantal === 1 ? ' wordt' : 'en worden') . ' weer getoond in de zijbalk', 1);
		return new JsonResponse(true);
	}

	/**
	 * Niveau voor meldingen instellen.
	 *
	 * @param int $draad_id
	 * @param string $niveau
	 *
	 * @return View
	 * @throws CsrGebruikerException
	 * @throws CsrException
	 */
	public function meldingsniveau(int $draad_id, $niveau) {
		$draad = $this->forumDradenModel->get($draad_id);
		if (!$draad || !$draad->magLezen() || !$draad->magMeldingKrijgen()) {
			throw new CsrToegangException('Onderwerp mag geen melding voor ontvangen worden');
		}
		if (!ForumDraadMeldingNiveau::isOptie($niveau)) {
			throw new CsrToegangException('Ongeldig meldingsniveau gespecificeerd');
		}
		$this->forumDradenMeldingRepository->setNiveauVoorLid($draad, $niveau);
		return new JsonResponse(true);
	}

	/**
	 * Niveau voor meldingen deelforum instellen
	 *
	 * @param int $forum_id
	 * @param string $niveau
	 *
	 * @return View
	 * @throws CsrGebruikerException
	 * @throws CsrException
	 */
	public function deelmelding(int $forum_id, $niveau) {
		$deel = $this->forumDelenModel->get($forum_id);
		if (!$deel || !$deel->magLezen() || !$deel->magMeldingKrijgen()) {
			throw new CsrToegangException('Deel mag geen melding voor ontvangen worden');
		}
		if ($niveau !== 'aan' && $niveau !== 'uit') {
			throw new CsrToegangException('Ongeldig meldingsniveau gespecificeerd');
		}
		$this->forumDelenMeldingRepository->setMeldingVoorLid($deel, $niveau === 'aan');
		return new JsonResponse(true);
	}

	/**
	 * Leg bladwijzer
	 *
	 * @param int $draad_id
	 * @throws CsrGebruikerException
	 */
	public function bladwijzer(int $draad_id) {
		$draad = $this->forumDradenModel->get($draad_id);
		$timestamp = (int)filter_input(INPUT_POST, 'timestamp', FILTER_SANITIZE_NUMBER_INT);
		if ($this->forumDradenGelezenRepository->setWanneerGelezenDoorLid($draad, $timestamp - 1)) {
			echo '<img id="timestamp' . $timestamp . '" src="/plaetjes/famfamfam/tick.png" class="icon" title="Bladwijzer succesvol geplaatst">';
		}
		exit; //TODO: JsonResponse
	}

	/**
	 * Wijzig een eigenschap van een draadje.
	 *
	 * @param int $draad_id
	 * @param string $property
	 * @return View|RedirectResponse|null
	 * @throws CsrException
	 * @throws CsrGebruikerException
	 * @throws CsrToegangException
	 */
	public function wijzigen(int $draad_id, $property) {
		$draad = $this->forumDradenModel->get($draad_id);
		// gedeelde moderators mogen dit niet
		if (!$draad->getForumDeel()->magModereren()) {
			throw new CsrToegangException();
		}
		if (in_array($property, array('verwijderd', 'gesloten', 'plakkerig', 'eerste_post_plakkerig', 'pagina_per_post'))) {
			$value = !$draad->$property;
			if ($property === 'belangrijk' && !LoginModel::mag(P_FORUM_BELANGRIJK)) {
				throw new CsrToegangException();
			}
		} elseif ($property === 'forum_id' || $property === 'gedeeld_met') {
			$value = (int)filter_input(INPUT_POST, $property, FILTER_SANITIZE_NUMBER_INT);
			if ($property === 'forum_id') {
				$deel = $this->forumDelenModel->get($value);
				if (!$deel->magModereren()) {
					throw new CsrToegangException();
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
			throw new CsrToegangException("Kan draad niet wijzigen");
		}
		$this->forumDradenModel->wijzigForumDraad($draad, $property, $value);
		if (is_bool($value)) {
			$wijziging = ($value ? 'wel ' : 'niet ') . $property;
		} else {
			$wijziging = $property . ' = ' . $value;
		}
		setMelding('Wijziging geslaagd: ' . $wijziging, 1);
		if ($property === 'belangrijk' || $property === 'forum_id' || $property === 'titel' || $property === 'gedeeld_met') {
			return $this->redirectToRoute('forum-onderwerp', ['draad_id' => $draad_id]);
		} else {
			return new JsonResponse(true);
		}
	}

	/**
	 * Forum post toevoegen en evt. nieuw draadje aanmaken.
	 * @TODO refactor deze veel te ingewikkelde functie en splits in meerdere functies, bijvoorbeeld in het ForumPostsModel
	 *
	 * @param int $forum_id
	 * @param int|null $draad_id
	 * @return RedirectResponse
	 * @throws CsrException
	 * @throws CsrGebruikerException
	 * @throws CsrToegangException
	 */
	public function posten(int $forum_id, $draad_id = null) {
		$deel = $this->forumDelenModel->get($forum_id);
		$draad = null;
		// post in bestaand draadje?
		$titel = null;
		if ($draad_id !== null) {
			$draad = $this->forumDradenModel->get($draad_id);

			// check draad in forum deel
			if (!$draad || $draad->forum_id !== $deel->forum_id || !$draad->magPosten()) {
				throw new CsrToegangException('Draad bestaat niet');
			}
			$redirect = $this->redirectToRoute('forum-onderwerp', ['draad_id' => $draad->draad_id]);
			$nieuw = false;
		} else {
			if (!$deel->magPosten()) {
				throw new CsrToegangException('Mag niet posten');
			}
			$redirect = $this->redirectToRoute('forum-deel', ['forum_id' => $deel->forum_id]);
			$nieuw = true;

			$titel = trim(filter_input(INPUT_POST, 'titel', FILTER_SANITIZE_STRING));
		}
		$tekst = trim(filter_input(INPUT_POST, 'forumBericht', FILTER_UNSAFE_RAW));

		// spam controle
		$filter = new SimpleSpamfilter();
		$spamtrap = filter_input(INPUT_POST, 'firstname', FILTER_UNSAFE_RAW);
		if (!empty($spamtrap) || ($tekst && $filter->isSpam($tekst)) || (isset($titel) && $titel && $filter->isSpam($titel))) {
			$this->debugLogModel->log(static::class, 'posten', [$forum_id, $draad_id], 'SPAM ' . $tekst);
			setMelding('SPAM', -1);
			throw new CsrToegangException("");
		}

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

		// concept opslaan
		if ($draad == null) {
			$this->forumDradenReagerenRepository->setConcept($deel, null, $tekst, $titel);
		} else {
			$this->forumDradenReagerenRepository->setConcept($deel, $draad->draad_id, $tekst);
		}


		// externen checks
		$mailadres = null;
		$wacht_goedkeuring = false;
		if (!LoginModel::mag(P_LOGGED_IN)) {
			$wacht_goedkeuring = true;
			$mailadres = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
			if (!email_like($mailadres)) {
				setMelding('U moet een geldig e-mailadres opgeven!', -1);
				return $redirect;
			}
			if ($filter->isSpam($mailadres)) { //TODO: logging
				setMelding('SPAM', -1);
				throw new CsrToegangException('SPAM');
			}
		}

		// post in nieuw draadje?
		if ($nieuw) {
			if (empty($titel)) {
				setMelding('U moet een titel opgeven!', -1);
				return $redirect;
			}
			// maak draad
			$draad = $this->forumDradenModel->maakForumDraad($deel->forum_id, $titel, $wacht_goedkeuring);
		}

		// maak post
		$post = $this->forumPostsModel->maakForumPost($draad->draad_id, $tekst, $_SERVER['REMOTE_ADDR'], $wacht_goedkeuring, $mailadres);

		// bericht sturen naar pubcie@csrdelft dat er een bericht op goedkeuring wacht?
		if ($wacht_goedkeuring) {
			setMelding('Uw bericht is opgeslagen en zal als het goedgekeurd is geplaatst worden.', 1);

			mail('pubcie@csrdelft.nl', 'Nieuw bericht wacht op goedkeuring', CSR_ROOT . "/forum/onderwerp/" . $draad->draad_id . "/wacht#" . $post->post_id . "\n\nDe inhoud van het bericht is als volgt: \n\n" . str_replace('\r\n', "\n", $tekst) . "\n\nEINDE BERICHT", "From: pubcie@csrdelft.nl\r\nReply-To: " . $mailadres);
		} else {

			// direct goedkeuren voor ingelogd
			$this->forumPostsModel->goedkeurenForumPost($post);
			$this->forumDradenMeldingRepository->stuurMeldingen($post);
			if ($nieuw) {
				$this->forumDelenMeldingRepository->stuurMeldingen($post);
			}
			setMelding(($nieuw ? 'Draad' : 'Post') . ' succesvol toegevoegd', 1);
			if ($nieuw && lid_instelling('forum', 'meldingEigenDraad') === 'ja') {
				$this->forumDradenMeldingRepository->setNiveauVoorLid($draad, ForumDraadMeldingNiveau::ALTIJD);
			}

			$redirect = $this->redirectToRoute('forum-reactie', ['post_id' => $post->post_id, '_fragment' => $post->post_id]);
		}

		// concept wissen
		if ($nieuw) {
			$this->forumDradenReagerenRepository->setConcept($deel);
		} else {
			$this->forumDradenReagerenRepository->setConcept($deel, $draad->draad_id);
		}

		// markeer als gelezen
		if (LoginModel::mag(P_LOGGED_IN)) {
			$this->forumDradenGelezenRepository->setWanneerGelezenDoorLid($draad);
		}

		// voorkom dubbelposts
		$_SESSION['forum_laatste_post_tekst'] = $tekst;

		// redirect naar post
		return $redirect;
	}

	/**
	 * @param $post_id
	 * @throws CsrGebruikerException
	 * @throws CsrToegangException
	 */
	public function citeren($post_id) {
		$post = $this->forumPostsModel->get((int)$post_id);
		if (!$post->magCiteren()) {
			throw new CsrToegangException("Mag niet citeren");
		}
		echo $this->forumPostsModel->citeerForumPost($post);
		exit; //TODO: JsonResponse
	}

	/**
	 * @param $post_id
	 * @throws CsrGebruikerException
	 * @throws CsrToegangException
	 */
	public function tekst($post_id) {
		$post = $this->forumPostsModel->get((int)$post_id);
		if (!$post->magBewerken()) {
			throw new CsrToegangException("Mag niet berwerken");
		}
		echo $post->tekst;
		exit; //TODO: JsonResponse
	}

	/**
	 * @param $post_id
	 * @return View
	 * @throws CsrException
	 * @throws CsrGebruikerException
	 */
	public function bewerken($post_id) {
		$post = $this->forumPostsModel->get((int)$post_id);
		if (!$post->magBewerken()) {
			throw new CsrToegangException("Mag niet bewerken");
		}
		$tekst = trim(filter_input(INPUT_POST, 'forumBericht', FILTER_UNSAFE_RAW));
		$reden = trim(filter_input(INPUT_POST, 'reden', FILTER_SANITIZE_STRING));
		$this->forumPostsModel->bewerkForumPost($tekst, $reden, $post);
		$this->forumDradenGelezenRepository->setWanneerGelezenDoorLid($post->getForumDraad(), strtotime($post->laatst_gewijzigd));
		return view('forum.partial.post_lijst', ['post' => $post]);
	}

	/**
	 * @param $post_id
	 * @return View
	 * @throws CsrException
	 * @throws CsrGebruikerException
	 */
	public function verplaatsen($post_id) {
		$post = $this->forumPostsModel->get((int)$post_id);
		$oudDraad = $post->getForumDraad();
		if (!$oudDraad->magModereren()) {
			throw new CsrToegangException("Geen moderator");
		}
		$nieuw = filter_input(INPUT_POST, 'Draad_id', FILTER_SANITIZE_NUMBER_INT);
		$nieuwDraad = $this->forumDradenModel->get((int)$nieuw);
		if (!$nieuwDraad->magModereren()) {
			throw new CsrToegangException("Geen moderator");
		}
		$this->forumPostsModel->verplaatsForumPost($nieuwDraad, $post);
		$this->forumPostsModel->goedkeurenForumPost($post);
		return view('forum.partial.post_delete', ['post' => $post]);
	}

	/**
	 * @param $post_id
	 * @return View
	 * @throws CsrException
	 * @throws CsrGebruikerException
	 */
	public function verwijderen($post_id) {
		$post = $this->forumPostsModel->get((int)$post_id);
		if (!$post->getForumDraad()->magModereren()) {
			throw new CsrToegangException("Geen moderator");
		}
		$this->forumPostsModel->verwijderForumPost($post);
		return view('forum.partial.post_delete', ['post' => $post]);
	}

	/**
	 * @param $post_id
	 * @return View
	 * @throws CsrException
	 * @throws CsrGebruikerException
	 */
	public function offtopic($post_id) {
		$post = $this->forumPostsModel->get((int)$post_id);
		if (!$post->getForumDraad()->magModereren()) {
			throw new CsrToegangException("Geen moderator");
		}
		$this->forumPostsModel->offtopicForumPost($post);
		return view('forum.partial.post_lijst', ['post' => $post]);
	}

	/**
	 * @param $post_id
	 * @return View
	 * @throws CsrException
	 * @throws CsrGebruikerException
	 */
	public function goedkeuren($post_id) {
		$post = $this->forumPostsModel->get((int)$post_id);
		if (!$post->getForumDraad()->magModereren()) {
			throw new CsrToegangException("Geen moderator");
		}
		$this->forumPostsModel->goedkeurenForumPost($post);
		return view('forum.partial.post_lijst', ['post' => $post]);
	}

	/**
	 * Concept bericht opslaan
	 * @param $forum_id
	 * @param null $draad_id
	 * @return View
	 * @throws CsrGebruikerException
	 * @throws CsrToegangException
	 */
	public function concept($forum_id, $draad_id = null) {
		$titel = trim(filter_input(INPUT_POST, 'titel', FILTER_SANITIZE_STRING));
		$concept = trim(filter_input(INPUT_POST, 'forumBericht', FILTER_UNSAFE_RAW));
		$ping = filter_input(INPUT_POST, 'ping', FILTER_SANITIZE_STRING);

		$deel = $this->forumDelenModel->get((int)$forum_id);
		// bestaand draadje?
		if ($draad_id !== null) {
			$draad = $this->forumDradenModel->get((int)$draad_id);
			$draad_id = $draad->draad_id;
			// check draad in forum deel
			if (!$draad || $draad->forum_id !== $deel->forum_id || !$draad->magPosten()) {
				throw new CsrToegangException("Draad bevindt zich niet in deel");
			}
			if (empty($ping)) {
				$this->forumDradenReagerenRepository->setConcept($deel, $draad_id, $concept);
			} elseif ($ping === 'true') {
				$this->forumDradenReagerenRepository->setWanneerReagerenDoorLid($deel, $draad_id);
			}
			$reageren = $this->forumDradenReagerenRepository->getReagerenVoorDraad($draad);
		} else {
			if (!$deel->magPosten()) {
				throw new CsrToegangException("Mag niet posten");
			}
			if (empty($ping)) {
				$this->forumDradenReagerenRepository->setConcept($deel, null, $concept, $titel);
			} elseif ($ping === 'true') {
				$this->forumDradenReagerenRepository->setWanneerReagerenDoorLid($deel);
			}
			$reageren = $this->forumDradenReagerenRepository->getReagerenVoorDeel($deel);
		}

		return view('forum.partial.draad_reageren', [
			'reageren' => $reageren
		]);
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
			'label' => $draad->getForumDeel()->titel,
			'value' => $draad->titel
		];
	}
}
