<?php

namespace CsrDelft\controller\forum;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\SimpleSpamFilter;
use CsrDelft\common\Util\InstellingUtil;
use CsrDelft\common\Util\MeldingUtil;
use CsrDelft\common\Util\UrlUtil;
use CsrDelft\controller\AbstractController;
use CsrDelft\entity\forum\ForumDeel;
use CsrDelft\entity\forum\ForumDraad;
use CsrDelft\entity\forum\ForumDraadMeldingNiveau;
use CsrDelft\entity\forum\ForumPost;
use CsrDelft\repository\DebugLogRepository;
use CsrDelft\repository\forum\ForumDelenMeldingRepository;
use CsrDelft\repository\forum\ForumDelenRepository;
use CsrDelft\repository\forum\ForumDradenGelezenRepository;
use CsrDelft\repository\forum\ForumDradenMeldingRepository;
use CsrDelft\repository\forum\ForumDradenReagerenRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\service\forum\ForumDelenService;
use CsrDelft\service\forum\ForumMeldingenService;
use CsrDelft\service\forum\ForumPostsService;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\bbcode\BbToProsemirror;
use CsrDelft\view\bbcode\ProsemirrorToBb;
use CsrDelft\view\forum\ForumSnelZoekenForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ForumDraadController extends AbstractController
{
	/**
	 * @var ForumPostsRepository
	 */
	private $forumPostsRepository;
	/**
	 * @var ForumDradenReagerenRepository
	 */
	private $forumDradenReagerenRepository;
	/**
	 * @var ForumDelenRepository
	 */
	private $forumDelenRepository;
	/**
	 * @var BbToProsemirror
	 */
	private $bbToProsemirror;
	/**
	 * @var ForumDradenGelezenRepository
	 */
	private $forumDradenGelezenRepository;
	/**
	 * @var ProsemirrorToBb
	 */
	private $prosemirrorToBb;
	/**
	 * @var DebugLogRepository
	 */
	private $debugLogRepository;
	/**
	 * @var ForumDradenRepository
	 */
	private $forumDradenRepository;
	/**
	 * @var ForumDradenMeldingRepository
	 */
	private $forumDradenMeldingRepository;
	/**
	 * @var ForumDelenMeldingRepository
	 */
	private $forumDelenMeldingRepository;
	/**
	 * @var ForumPostsService
	 */
	private $forumPostsService;
	/**
	 * @var ForumDelenService
	 */
	private $forumDelenService;
	/**
	 * @var ForumMeldingenService
	 */
	private $forumMeldingenService;

	public function __construct(
		ForumPostsRepository $forumPostsRepository,
		ForumDradenReagerenRepository $forumDradenReagerenRepository,
		ForumDelenRepository $forumDelenRepository,
		ForumDradenGelezenRepository $forumDradenGelezenRepository,
		ProsemirrorToBb $prosemirrorToBb,
		DebugLogRepository $debugLogRepository,
		ForumDradenRepository $forumDradenRepository,
		ForumDelenService $forumDelenService,
		ForumPostsService $forumPostsService,
		ForumMeldingenService $forumMeldingenService,
		ForumDradenMeldingRepository $forumDradenMeldingRepository,
		ForumDelenMeldingRepository $forumDelenMeldingRepository,
		BbToProsemirror $bbToProsemirror
	) {
		$this->forumPostsRepository = $forumPostsRepository;
		$this->forumDradenReagerenRepository = $forumDradenReagerenRepository;
		$this->forumDelenRepository = $forumDelenRepository;
		$this->bbToProsemirror = $bbToProsemirror;
		$this->forumDradenGelezenRepository = $forumDradenGelezenRepository;
		$this->prosemirrorToBb = $prosemirrorToBb;
		$this->debugLogRepository = $debugLogRepository;
		$this->forumDradenRepository = $forumDradenRepository;
		$this->forumDradenMeldingRepository = $forumDradenMeldingRepository;
		$this->forumDelenMeldingRepository = $forumDelenMeldingRepository;
		$this->forumPostsService = $forumPostsService;
		$this->forumDelenService = $forumDelenService;
		$this->forumMeldingenService = $forumMeldingenService;
	}

	/**
	 * Opzoeken forumdraad van forumpost.
	 *
	 * @param RequestStack $requestStack
	 * @param ForumPost $post
	 * @return Response
	 * @Route("/forum/reactie/{post_id}", methods={"GET"})
	 * @Auth(P_PUBLIC)
	 */
	public function reactie(RequestStack $requestStack, ForumPost $post): Response
	{
		if ($post->verwijderd) {
			MeldingUtil::setMelding('Deze reactie is verwijderd', 0);
		}
		return $this->onderwerp(
			$requestStack,
			$post->draad,
			$this->forumPostsRepository->getPaginaVoorPost($post)
		);
	}

	/**
	 * Forumdraadje laten zien met alle zichtbare/verwijderde posts.
	 *
	 * @param RequestStack $requestStack
	 * @param ForumDraad $draad
	 * @param int|null $pagina or 'laatste' or 'ongelezen'
	 * @param string|null $statistiek
	 * @return Response
	 * @Route("/forum/onderwerp/{draad_id}/{pagina}/{statistiek}", methods={"GET"}, defaults={"pagina"=null,"statistiek"=null})
	 * @Auth(P_PUBLIC)
	 */
	public function onderwerp(
		RequestStack $requestStack,
		ForumDraad $draad,
		$pagina = null,
		$statistiek = null
	): Response {
		if (!$draad->magLezen()) {
			throw $this->createAccessDeniedException();
		}
		if ($this->mag(P_LOGGED_IN)) {
			$gelezen = $draad->getWanneerGelezen();
		} else {
			$gelezen = null;
		}
		if ($pagina === null) {
			$pagina = InstellingUtil::lid_instelling('forum', 'open_draad_op_pagina');
		}
		$paging = true;
		if ($pagina === 'ongelezen' && $gelezen) {
			$this->forumPostsRepository->setPaginaVoorLaatstGelezen($gelezen);
		} elseif ($pagina === 'laatste') {
			$this->forumPostsRepository->setLaatstePagina($draad->draad_id);
		} elseif ($pagina === 'prullenbak' && $draad->magModereren()) {
			$draad->setForumPosts(
				$this->forumPostsRepository->getPrullenbakVoorDraad($draad)
			);
			$paging = false;
		} else {
			$this->forumPostsRepository->setHuidigePagina(
				(int) $pagina,
				$draad->draad_id
			);
		}

		if ($this->getUser()) {
			$concept = $this->forumDradenReagerenRepository->getConcept(
				$draad->deel,
				$draad->draad_id
			);
		} else {
			$concept = $requestStack->getSession()->remove('forum_bericht');
		}

		$draad_ongelezen = $gelezen ? $draad->isOngelezen() : true;
		$gelezen_moment = $gelezen ? $gelezen->datum_tijd : false;

		if ($this->mag(P_LOGGED_IN)) {
			$this->forumDradenGelezenRepository->setWanneerGelezenDoorLid($draad);
		}

		$view = $this->render('forum/draad.html.twig', [
			'zoekform' => new ForumSnelZoekenForm(),
			'draad' => $draad,
			'paging' =>
				$paging &&
				$this->forumPostsRepository->getAantalPaginas($draad->draad_id) > 1,
			'post_form_tekst' => $this->bbToProsemirror->toProseMirror($concept),
			'reageren' => $this->forumDradenReagerenRepository->getReagerenVoorDraad(
				$draad
			),
			'categorien' => $this->forumDelenService->getForumIndelingVoorLid(),
			'gedeeld_met_opties' => $this->forumDelenRepository->getForumDelenOptiesOmTeDelen(
				$draad->deel
			),
			'statistiek' =>
				$statistiek === 'statistiek' && $draad->magStatistiekBekijken(),
			'draad_ongelezen' => $draad_ongelezen,
			'gelezen_moment' => $gelezen_moment,
		]);

		return $view;
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
	public function wijzigen(ForumDraad $draad, $property)
	{
		// gedeelde moderators mogen dit niet
		if (!$draad->deel->magModereren()) {
			throw $this->createAccessDeniedException();
		}
		if (
			in_array($property, [
				'verwijderd',
				'gesloten',
				'plakkerig',
				'eerste_post_plakkerig',
				'pagina_per_post',
			])
		) {
			$value = !$draad->$property;
			if ($property === 'belangrijk' && !$this->mag(P_FORUM_BELANGRIJK)) {
				throw $this->createAccessDeniedException();
			}
		} elseif ($property === 'forum_id' || $property === 'gedeeld_met') {
			$value = (int) filter_input(
				INPUT_POST,
				$property,
				FILTER_SANITIZE_NUMBER_INT
			);
			if ($property === 'forum_id') {
				$deel = $this->forumDelenRepository->get($value);
				if (!$deel->magModereren()) {
					throw $this->createAccessDeniedException();
				}
			} elseif ($value === 0) {
				$value = null;
			}
		} elseif ($property === 'titel' || $property === 'belangrijk') {
			$value = trim(
				filter_input(INPUT_POST, $property, FILTER_SANITIZE_STRING)
			);
			if (empty($value)) {
				$value = null;
			}
		} else {
			throw $this->createAccessDeniedException('Kan draad niet wijzigen');
		}
		$this->forumPostsService->wijzigForumDraad($draad, $property, $value);
		if (is_bool($value)) {
			$wijziging = ($value ? 'wel ' : 'niet ') . $property;
		} else {
			$wijziging = $property . ' = ' . $value;
		}
		MeldingUtil::setMelding('Wijziging geslaagd: ' . $wijziging, 1);
		if (
			$property === 'belangrijk' ||
			$property === 'forum_id' ||
			$property === 'titel' ||
			$property === 'gedeeld_met'
		) {
			return $this->redirectToRoute('csrdelft_forum_forumdraad_onderwerp', [
				'draad_id' => $draad->draad_id,
			]);
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
	public function posten(
		RequestStack $requestStack,
		ForumDeel $deel,
		ForumDraad $draad = null
	) {
		// post in bestaand draadje?
		$titel = null;
		if ($draad !== null) {
			// check draad in forum deel
			if (
				!$draad ||
				$draad->forum_id !== $deel->forum_id ||
				!$draad->magPosten()
			) {
				throw $this->createAccessDeniedException('Draad bestaat niet');
			}
			$redirect = $this->redirectToRoute(
				'csrdelft_forum_forumdraad_onderwerp',
				['draad_id' => $draad->draad_id]
			);
			$nieuw = false;
		} else {
			if (!$deel->magPosten()) {
				throw $this->createAccessDeniedException('Mag niet posten');
			}
			$redirect = $this->redirectToRoute('csrdelft_forum_forumdeel_deel', [
				'forum_id' => $deel->forum_id,
			]);
			$nieuw = true;

			$titel = trim(filter_input(INPUT_POST, 'titel', FILTER_SANITIZE_STRING));
		}
		$tekst = $this->prosemirrorToBb->convertToBb(
			json_decode(
				trim(filter_input(INPUT_POST, 'forumBericht', FILTER_UNSAFE_RAW))
			)
		);

		if (empty($tekst)) {
			MeldingUtil::setMelding('Bericht mag niet leeg zijn', -1);
			return $redirect;
		}

		// voorkom dubbelposts
		if (
			isset($_SESSION['forum_laatste_post_tekst']) &&
			$_SESSION['forum_laatste_post_tekst'] === $tekst
		) {
			MeldingUtil::setMelding('Uw reactie is al geplaatst', 0);

			// concept wissen
			if ($nieuw) {
				$this->forumDradenReagerenRepository->setConcept($deel);
			} else {
				$this->forumDradenReagerenRepository->setConcept(
					$deel,
					$draad->draad_id
				);
			}

			return $redirect;
		}

		if ($this->mag(P_LOGGED_IN)) {
			// concept opslaan
			if ($draad == null) {
				$this->forumDradenReagerenRepository->setConcept(
					$deel,
					null,
					$tekst,
					$titel
				);
			} else {
				$this->forumDradenReagerenRepository->setConcept(
					$deel,
					$draad->draad_id,
					$tekst
				);
			}
		}

		// externen checks
		$mailadres = null;
		$wacht_goedkeuring = false;
		if (!$this->mag(P_LOGGED_IN)) {
			$filter = new SimpleSpamfilter();
			$spamtrap = filter_input(INPUT_POST, 'firstname', FILTER_UNSAFE_RAW);

			if (
				!empty($spamtrap) ||
				($tekst && $filter->isSpam($tekst)) ||
				(isset($titel) && $titel && $filter->isSpam($titel))
			) {
				$this->debugLogRepository->log(
					static::class,
					'posten',
					[$deel->forum_id, $draad->draad_id],
					'SPAM ' . $tekst
				);
				MeldingUtil::setMelding('SPAM', -1);
				throw $this->createAccessDeniedException('');
			}

			$wacht_goedkeuring = true;
			$mailadres = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
			if (!UrlUtil::email_like($mailadres)) {
				MeldingUtil::setMelding('U moet een geldig e-mailadres opgeven!', -1);
				$requestStack->getSession()->set('forum_bericht', $tekst);
				return $redirect;
			}
			if ($filter->isSpam($mailadres)) {
				//TODO: logging
				MeldingUtil::setMelding('SPAM', -1);
				throw $this->createAccessDeniedException('SPAM');
			}
		}

		// post in nieuw draadje?
		if ($nieuw) {
			if (empty($titel)) {
				MeldingUtil::setMelding('U moet een titel opgeven!', -1);
				return $redirect;
			}
			// maak draad
			$draad = $this->forumDradenRepository->maakForumDraad(
				$deel,
				$titel,
				$wacht_goedkeuring
			);
		}

		// maak post
		$post = $this->forumPostsRepository->maakForumPost(
			$draad,
			$tekst,
			$_SERVER['REMOTE_ADDR'],
			$wacht_goedkeuring,
			$mailadres
		);

		// bericht sturen naar pubcie@csrdelft dat er een bericht op goedkeuring wacht?
		if ($wacht_goedkeuring) {
			MeldingUtil::setMelding(
				'Uw bericht is opgeslagen en zal als het goedgekeurd is geplaatst worden.',
				1
			);

			$url = $this->generateUrl('csrdelft_forum_forumdraad_onderwerp', [
				'draad_id' => $draad->draad_id,
				'_fragment' => $post->post_id,
			]);
			mail(
				'pubcie@csrdelft.nl',
				'Nieuw bericht wacht op goedkeuring',
				$url .
					"\n\nDe inhoud van het bericht is als volgt: \n\n" .
					str_replace('\r\n', "\n", $tekst) .
					"\n\nEINDE BERICHT",
				"From: pubcie@csrdelft.nl\r\nReply-To: " . $mailadres
			);
		} else {
			// direct goedkeuren voor ingelogd
			$this->forumPostsService->goedkeurenForumPost($post);
			$this->forumMeldingenService->stuurDraadMeldingen($post);
			if ($nieuw) {
				$this->forumMeldingenService->stuurDeelMeldingen($post);
			}
			MeldingUtil::setMelding(
				($nieuw ? 'Draad' : 'Post') . ' succesvol toegevoegd',
				1
			);
			if (
				$nieuw &&
				InstellingUtil::lid_instelling('forum', 'meldingEigenDraad') === 'ja'
			) {
				$this->forumDradenMeldingRepository->setNiveauVoorLid(
					$draad,
					ForumDraadMeldingNiveau::ALTIJD()
				);
			}

			$redirect = $this->redirectToRoute('csrdelft_forum_forumdraad_reactie', [
				'post_id' => $post->post_id,
				'_fragment' => $post->post_id,
			]);
		}

		// concept wissen
		if ($nieuw) {
			$this->forumDradenReagerenRepository->setConcept($deel);
		} else {
			$this->forumDradenReagerenRepository->setConcept($deel, $draad->draad_id);
		}

		// markeer als gelezen
		if ($this->mag(P_LOGGED_IN)) {
			$this->forumDradenGelezenRepository->setWanneerGelezenDoorLid(
				$draad,
				$post->laatst_gewijzigd
			);
		}

		// voorkom dubbelposts
		$_SESSION['forum_laatste_post_tekst'] = $tekst;

		// redirect naar post
		return $redirect;
	}
}
