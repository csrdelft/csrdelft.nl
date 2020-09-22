<?php

namespace CsrDelft\view;

use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\repository\fotoalbum\FotoAlbumRepository;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\VerjaardagenService;
use CsrDelft\view\fotoalbum\FotoAlbumZijbalkView;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 */
class Zijbalk {
	/**
	 * @var Environment
	 */
	private $twig;
	/**
	 * @var MenuItemRepository
	 */
	private $menuItemRepository;
	/**
	 * @var ForumDradenRepository
	 */
	private $forumDradenRepository;
	/**
	 * @var AgendaRepository
	 */
	private $agendaRepository;
	/**
	 * @var ForumPostsRepository
	 */
	private $forumPostsRepository;
	/**
	 * @var FotoAlbumRepository
	 */
	private $fotoAlbumRepository;
	/**
	 * @var VerjaardagenService
	 */
	private $verjaardagenService;
	/**
	 * @var LidInstellingenRepository
	 */
	private $lidInstellingenRepository;
	/**
	 * @var SessionInterface
	 */
	private $session;

	public function __construct(
		SessionInterface $session,
		Environment $twig,
		MenuItemRepository $menuItemRepository,
		ForumDradenRepository $forumDradenRepository,
		AgendaRepository $agendaRepository,
		ForumPostsRepository $forumPostsRepository,
		FotoAlbumRepository $fotoAlbumRepository,
		VerjaardagenService $verjaardagenService,
		LidInstellingenRepository $lidInstellingenRepository
	) {
		$this->twig = $twig;
		$this->menuItemRepository = $menuItemRepository;
		$this->forumDradenRepository = $forumDradenRepository;
		$this->agendaRepository = $agendaRepository;
		$this->forumPostsRepository = $forumPostsRepository;
		$this->fotoAlbumRepository = $fotoAlbumRepository;
		$this->verjaardagenService = $verjaardagenService;
		$this->lidInstellingenRepository = $lidInstellingenRepository;
		$this->session = $session;
	}

	/**
	 * @return string[]
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	public function getZijbalk() {
		$zijbalk = [];
		// Favorieten menu
		if (LoginService::mag(P_LOGGED_IN) && lid_instelling('zijbalk', 'favorieten') == 'ja') {
			$menu = $this->menuItemRepository->getMenu(LoginService::getUid());
			$menu->tekst = 'Favorieten';
			array_unshift($zijbalk, $this->twig->render('menu/block.html.twig', ['root' => $menu]));
		}
		// Is het al...
		if (lid_instelling('zijbalk', 'ishetal') != 'niet weergeven') {
			array_unshift($zijbalk, (new IsHetAlView($this->lidInstellingenRepository, $this->session, $this->agendaRepository, lid_instelling('zijbalk', 'ishetal')))->toString());
		}

		// Sponsors
		if (LoginService::mag(P_LOGGED_IN)) {
			$sponsor_menu = $this->menuItemRepository->getMenu("sponsors");
			if ($sponsor_menu) {
				$sponsor_menu->tekst = 'Mogelijkheden';
				$zijbalk[] = $this->twig->render('menu/block.html.twig', ['root' => $sponsor_menu]);
			}
		}

		// Agenda
		if (LoginService::mag(P_AGENDA_READ) && lid_instelling('zijbalk', 'agendaweken') > 0 && lid_instelling('zijbalk', 'agenda_max') > 0) {
			$aantalWeken = lid_instelling('zijbalk', 'agendaweken');
			$items = $this->agendaRepository->getAllAgendeerbaar(date_create_immutable(), date_create_immutable('next saturday + ' . $aantalWeken . ' weeks'), false, true);
			if (count($items) > lid_instelling('zijbalk', 'agenda_max')) {
				$items = array_slice($items, 0, lid_instelling('zijbalk', 'agenda_max'));
			}
			$zijbalk[] = $this->twig->render('agenda/zijbalk.html.twig', ['items' => $items]);
		}
		// Nieuwste belangrijke forumberichten
		if (lid_instelling('zijbalk', 'forum_belangrijk') > 0) {
			$zijbalk[] = $this->twig->render('forum/partial/draad_zijbalk.html.twig', [
				'draden' => $this->forumDradenRepository->getRecenteForumDraden((int)lid_instelling('zijbalk', 'forum_belangrijk'), true),
				'aantalWacht' => $this->forumPostsRepository->getAantalWachtOpGoedkeuring(),
				'belangrijk' => true
			]);
		}
		// Nieuwste forumberichten
		if (lid_instelling('zijbalk', 'forum') > 0) {
			$belangrijk = (lid_instelling('zijbalk', 'forum_belangrijk') > 0 ? false : null);
			$zijbalk[] = $this->twig->render('forum/partial/draad_zijbalk.html.twig', [
				'draden' => $this->forumDradenRepository->getRecenteForumDraden((int)lid_instelling('zijbalk', 'forum'), $belangrijk),
				'aantalWacht' => $this->forumPostsRepository->getAantalWachtOpGoedkeuring(),
				'belangrijk' => $belangrijk
			]);
		}
		// Zelfgeposte forumberichten
		if (lid_instelling('zijbalk', 'forum_zelf') > 0) {
			$posts = $this->forumPostsRepository->getRecenteForumPostsVanLid(LoginService::getUid(), (int)lid_instelling('zijbalk', 'forum_zelf'), true);
			$zijbalk[] = $this->twig->render('forum/partial/post_zijbalk.html.twig', ['posts' => $posts]);
		}
		// Nieuwste fotoalbum
		if (lid_instelling('zijbalk', 'fotoalbum') == 'ja') {
			$album = $this->fotoAlbumRepository->getMostRecentFotoAlbum();
			if ($album !== null) {
				$zijbalk[] = $this->twig->render('fotoalbum/zijbalk.html.twig', ['album' => $album, 'jaargang' => LichtingenRepository::getHuidigeJaargang()]);
			}
		}
		// Komende verjaardagen
		if (LoginService::mag(P_LOGGED_IN) && lid_instelling('zijbalk', 'verjaardagen') > 0) {
			$zijbalk[] = $this->twig->render('verjaardagen/komende.html.twig', [
				'verjaardagen' => $this->verjaardagenService->getKomende((int)lid_instelling('zijbalk', 'verjaardagen')),
				'toonpasfotos' => lid_instelling('zijbalk', 'verjaardagen_pasfotos') == 'ja',
			]);
		}
		return $zijbalk;
	}

}
