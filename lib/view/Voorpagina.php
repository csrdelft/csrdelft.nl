<?php

namespace CsrDelft\view;

use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\repository\fotoalbum\FotoAlbumRepository;
use CsrDelft\repository\groepen\LichtingenRepository;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\repository\WoordVanDeDagRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\VerjaardagenService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

/**
 * @author Daniël
 *
 */
class Voorpagina {
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
	 * @var WoordVanDeDagRepository
	 */
	private $woordVanDeDagRepository;
	/**
	 * @var LidInstellingenRepository
	 */
	private $lidInstellingenRepository;
	/**
	 * @var RequestStack
	 */
	private $requestStack;

	public function __construct(
		RequestStack $requestStack,
		Environment $twig,
		MenuItemRepository $menuItemRepository,
		ForumDradenRepository $forumDradenRepository,
		AgendaRepository $agendaRepository,
		ForumPostsRepository $forumPostsRepository,
		FotoAlbumRepository $fotoAlbumRepository,
		VerjaardagenService $verjaardagenService,
		LidInstellingenRepository $lidInstellingenRepository,
		WoordVanDeDagRepository $woordVanDeDagRepository
	) {
		$this->twig = $twig;
		$this->menuItemRepository = $menuItemRepository;
		$this->forumDradenRepository = $forumDradenRepository;
		$this->agendaRepository = $agendaRepository;
		$this->forumPostsRepository = $forumPostsRepository;
		$this->fotoAlbumRepository = $fotoAlbumRepository;
		$this->verjaardagenService = $verjaardagenService;
		$this->lidInstellingenRepository = $lidInstellingenRepository;
		$this->woordVanDeDagRepository = $woordVanDeDagRepository;
		$this->requestStack = $requestStack;
	}


	public function getIsHetAl(): ?string
	{
		return (new IsHetAlView($this->lidInstellingenRepository, $this->requestStack, $this->agendaRepository, $this->woordVanDeDagRepository, lid_instelling('zijbalk', 'ishetal')))->__toString();
//		if (lid_instelling('zijbalk', 'ishetal') != 'niet weergeven') {
//			return (new IsHetAlView($this->lidInstellingenRepository, $this->requestStack, $this->agendaRepository, $this->woordVanDeDagRepository, lid_instelling('zijbalk', 'ishetal')))->__toString();
//		}
//
//		return null;
	}

	public function getVerjaardagen(): ?string
	{
		// Komende verjaardagen
		if (LoginService::mag(P_LOGGED_IN)) {
			return $this->twig->render('voorpagina/verjaardagen.html.twig', [
				'verjaardagen' => $this->verjaardagenService->getKomende(6),
				true,
			]);
		}

		return null;
	}

	public function getOverig(): ?string
	{
		return $this->twig->render('voorpagina/overig.html.twig');
	}

	public function getFotoalbum(): ?string
	{
		// Nieuwste fotoalbum
		$album = $this->fotoAlbumRepository->getMostRecentFotoAlbum();
		if ($album !== null) {
			return $this->twig->render('voorpagina/fotoalbum.html.twig', ['album' => $album, 'jaargang' => LichtingenRepository::getHuidigeJaargang()]);
		}

		return null;
	}



	/**
	 * @return string
	 */
	public function getAgenda(): ?string
	{
		// Agenda
		if (LoginService::mag(P_AGENDA_READ)) {
			$aantalWeken = lid_instelling('zijbalk', 'agendaweken');
			$items = $this->agendaRepository->getAllAgendeerbaar(date_create_immutable(), date_create_immutable('next saturday + ' . $aantalWeken . ' weeks'), false, true);
			// TODO: nog uit de instellingen halen
			// if (count($items) > lid_instelling('zijbalk', 'agenda_max')) {
			// 	$items = array_slice($items, 0, lid_instelling('zijbalk', 'agenda_max'));
			// }
			return $this->twig->render('voorpagina/agenda.html.twig', ['items' => $items]);
		}

		return null;
	}

	public function getForum()
	{
		$belangrijk = true;
		return $this->twig->render('voorpagina/forum.html.twig', [
			'draden' => $this->forumDradenRepository->getRecenteForumDraden((int)lid_instelling('zijbalk', 'forum'), $belangrijk),
			'aantalWacht' => $this->forumPostsRepository->getAantalWachtOpGoedkeuring(),
			'belangrijk' => $belangrijk
		]);
	}


	private function blockFavorieten() {
		// Favorieten menu
		if (LoginService::mag(P_LOGGED_IN) && lid_instelling('zijbalk', 'favorieten') == 'ja') {
			$menu = $this->menuItemRepository->getMenu(LoginService::getUid());
			$menu->tekst = 'Favorieten';
			return $this->twig->render('menu/block.html.twig', ['root' => $menu]);
		}

		return null;
	}

	private function blockSponsors() {
		// Sponsors
		if (LoginService::mag(P_LOGGED_IN)) {
			$sponsor_menu = $this->menuItemRepository->getMenu("sponsors");
			if ($sponsor_menu) {
				$sponsor_menu->tekst = 'Mogelijkheden';
				return $this->twig->render('menu/block.html.twig', ['root' => $sponsor_menu]);
			}
		}

		return null;
	}

	private function blockForumNieuwsteBelangrijkBerichten() {
		// Nieuwste belangrijke forumberichten
		if (lid_instelling('zijbalk', 'forum_belangrijk') > 0) {
			return $this->twig->render('voorpagina.html.twig', [
				'draden' => $this->forumDradenRepository->getRecenteForumDraden((int)lid_instelling('zijbalk', 'forum_belangrijk'), true),
				'aantalWacht' => $this->forumPostsRepository->getAantalWachtOpGoedkeuring(),
				'belangrijk' => true
			]);
		}

		return null;
	}

	private function blockForumNieuwsteBerichten() {
		// Nieuwste forumberichten
		if (lid_instelling('zijbalk', 'forum') > 0) {
			$belangrijk = (lid_instelling('zijbalk', 'forum_belangrijk') > 0 ? false : null);
			return $this->twig->render('voorpagina.html.twig', [
				'draden' => $this->forumDradenRepository->getRecenteForumDraden((int)lid_instelling('zijbalk', 'forum'), $belangrijk),
				'aantalWacht' => $this->forumPostsRepository->getAantalWachtOpGoedkeuring(),
				'belangrijk' => $belangrijk
			]);
		}

		return null;
	}

	private function blockForumZelfgepost() {
		// Zelfgeposte forumberichten
		if (lid_instelling('zijbalk', 'forum_zelf') > 0) {
			$posts = $this->forumPostsRepository->getRecenteForumPostsVanLid(LoginService::getUid(), (int)lid_instelling('zijbalk', 'forum_zelf'), true);
			return $this->twig->render('forum/partial/post_zijbalk.html.twig', ['posts' => $posts]);
		}

		return null;
	}

}
