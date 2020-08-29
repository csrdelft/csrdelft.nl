<?php

namespace CsrDelft\view;

use CsrDelft\common\ContainerFacade;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\repository\forum\ForumDradenRepository;
use CsrDelft\repository\forum\ForumPostsRepository;
use CsrDelft\repository\fotoalbum\FotoAlbumRepository;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\VerjaardagenService;
use CsrDelft\view\fotoalbum\FotoAlbumZijbalkView;

/**
 * Zijbalk.static.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 */
abstract class Zijbalk {

	/**
	 * @param string[] $zijbalk
	 * @return array
	 * @throws \Twig\Error\LoaderError
	 * @throws \Twig\Error\RuntimeError
	 * @throws \Twig\Error\SyntaxError
	 */
	public static function addStandaardZijbalk(array $zijbalk) {
		$twig = ContainerFacade::getContainer()->get('twig');
		$menuItemRepository = ContainerFacade::getContainer()->get(MenuItemRepository::class);
		// Favorieten menu
		if (LoginService::mag(P_LOGGED_IN) and lid_instelling('zijbalk', 'favorieten') == 'ja') {
			$menu = $menuItemRepository->getMenu(LoginService::getUid());
			$menu->tekst = 'Favorieten';
			array_unshift($zijbalk, view('menu.block', ['root' => $menu])->toString());
		}
		// Is het al...
		if (lid_instelling('zijbalk', 'ishetal') != 'niet weergeven') {
			array_unshift($zijbalk, (new IsHetAlView(lid_instelling('zijbalk', 'ishetal')))->toString());
		}

		// Sponsors
		if (LoginService::mag(P_LOGGED_IN)) {
			$sponsor_menu = $menuItemRepository->getMenu("sponsors");
			if ($sponsor_menu) {
				$sponsor_menu->tekst = 'Mogelijkheden';
				$zijbalk[] = view('menu.block', ['root' => $sponsor_menu])->toString();
			}
		}

		// Agenda
		if (LoginService::mag(P_AGENDA_READ) && lid_instelling('zijbalk', 'agendaweken') > 0 && lid_instelling('zijbalk', 'agenda_max') > 0) {
			$aantalWeken = lid_instelling('zijbalk', 'agendaweken');
			$agendaRepository = ContainerFacade::getContainer()->get(AgendaRepository::class);
			$items = $agendaRepository->getAllAgendeerbaar(date_create_immutable(), date_create_immutable('next saturday + ' . $aantalWeken . ' weeks'), false, true);
			if (count($items) > lid_instelling('zijbalk', 'agenda_max')) {
				$items = array_slice($items, 0, lid_instelling('zijbalk', 'agenda_max'));
			}
			$zijbalk[] = view('agenda.zijbalk', ['items' => $items])->toString();
		}
		$forumDradenRepository = ContainerFacade::getContainer()->get(ForumDradenRepository::class);
		$forumPostsRepository = ContainerFacade::getContainer()->get(ForumPostsRepository::class);
		// Nieuwste belangrijke forumberichten
		if (lid_instelling('zijbalk', 'forum_belangrijk') > 0) {
			$zijbalk[] = $twig->render('forum/partial/draad_zijbalk.html.twig', [
				'draden' => $forumDradenRepository->getRecenteForumDraden((int)lid_instelling('zijbalk', 'forum_belangrijk'), true),
				'aantalWacht' => $forumPostsRepository->getAantalWachtOpGoedkeuring(),
				'belangrijk' => true
			]);
		}
		// Nieuwste forumberichten
		if (lid_instelling('zijbalk', 'forum') > 0) {
			$belangrijk = (lid_instelling('zijbalk', 'forum_belangrijk') > 0 ? false : null);
			$zijbalk[] = $twig->render('forum/partial/draad_zijbalk.html.twig', [
				'draden' => $forumDradenRepository->getRecenteForumDraden((int)lid_instelling('zijbalk', 'forum'), $belangrijk),
				'aantalWacht' => $forumPostsRepository->getAantalWachtOpGoedkeuring(),
				'belangrijk' => $belangrijk
			]);
		}
		// Zelfgeposte forumberichten
		if (lid_instelling('zijbalk', 'forum_zelf') > 0) {
			$posts = $forumPostsRepository->getRecenteForumPostsVanLid(LoginService::getUid(), (int)lid_instelling('zijbalk', 'forum_zelf'), true);
			$zijbalk[] = $twig->render('forum/partial/post_zijbalk.html.twig', ['posts' => $posts]);
		}
		// Nieuwste fotoalbum
		if (lid_instelling('zijbalk', 'fotoalbum') == 'ja') {
			$album = ContainerFacade::getContainer()->get(FotoAlbumRepository::class)->getMostRecentFotoAlbum();
			if ($album !== null) {
				$zijbalk[] = (new FotoAlbumZijbalkView($album))->toString();
			}
		}
		// Komende verjaardagen
		if (LoginService::mag(P_LOGGED_IN) and lid_instelling('zijbalk', 'verjaardagen') > 0) {
			$verjaardagenService = ContainerFacade::getContainer()->get(VerjaardagenService::class);
			$zijbalk[] = $twig->render('verjaardagen/komende.html.twig', [
				'verjaardagen' => $verjaardagenService->getKomende((int)lid_instelling('zijbalk', 'verjaardagen')),
				'toonpasfotos' => lid_instelling('zijbalk', 'verjaardagen_pasfotos') == 'ja',
			]);
		}
		return $zijbalk;
	}

}
