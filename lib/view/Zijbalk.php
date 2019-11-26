<?php

namespace CsrDelft\view;

use CsrDelft\common\ContainerFacade;
use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\forum\ForumPostsModel;
use CsrDelft\model\fotoalbum\FotoAlbumModel;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\LedenMemoryScoresModel;
use CsrDelft\model\MenuModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\model\VerjaardagenModel;
use CsrDelft\repository\agenda\AgendaRepository;
use CsrDelft\view\fotoalbum\FotoAlbumZijbalkView;
use CsrDelft\view\ledenmemory\LedenMemoryZijbalkView;

/**
 * Zijbalk.static.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 */
abstract class Zijbalk {

	public static function addStandaardZijbalk(array $zijbalk) {
		// Favorieten menu
		if (LoginModel::mag(P_LOGGED_IN) AND lid_instelling('zijbalk', 'favorieten') == 'ja') {
			$menu = MenuModel::instance()->getMenu(LoginModel::getUid());
			$menu->tekst = 'Favorieten';
			array_unshift($zijbalk, view('menu.block', ['root' => $menu]));
		}
		// Is het al...
		if (lid_instelling('zijbalk', 'ishetal') != 'niet weergeven') {
			array_unshift($zijbalk, new IsHetAlView(lid_instelling('zijbalk', 'ishetal')));
		}

		// Sponsors
		if (LoginModel::mag(P_LOGGED_IN)) {
			$sponsor_menu = MenuModel::instance()->getMenu("sponsors");
			$sponsor_menu->tekst = 'Mogelijkheden';
			$zijbalk[] = view('menu.block', ['root' => $sponsor_menu]);
		}

		// Agenda
		if (LoginModel::mag(P_AGENDA_READ) && lid_instelling('zijbalk', 'agendaweken') > 0 && lid_instelling('zijbalk', 'agenda_max') > 0) {
			$aantalWeken = lid_instelling('zijbalk', 'agendaweken');
			$beginMoment = strtotime(date('Y-m-d'));
			$eindMoment = strtotime('+' . $aantalWeken . ' weeks', $beginMoment);
			$eindMoment = strtotime('next saturday', $eindMoment);
			$agendaRepository = ContainerFacade::getContainer()->get(AgendaRepository::class);
			$items = $agendaRepository->getAllAgendeerbaar($beginMoment, $eindMoment, false, true);
			if (count($items) > lid_instelling('zijbalk', 'agenda_max')) {
				$items = array_slice($items, 0, lid_instelling('zijbalk', 'agenda_max'));
			}
			$zijbalk[] = view('agenda.zijbalk', ['items' => $items]);
		}
		// Nieuwste belangrijke forumberichten
		if (lid_instelling('zijbalk', 'forum_belangrijk') > 0) {
			$zijbalk[] = view('forum.partial.draad_zijbalk', [
				'draden' => ForumDradenModel::instance()->getRecenteForumDraden((int)lid_instelling('zijbalk', 'forum_belangrijk'), true),
				'aantalWacht' => ForumPostsModel::instance()->getAantalWachtOpGoedkeuring(),
				'belangrijk' => true
			]);
		}
		// Nieuwste forumberichten
		if (lid_instelling('zijbalk', 'forum') > 0) {
			$belangrijk = (lid_instelling('zijbalk', 'forum_belangrijk') > 0 ? false : null);
			$zijbalk[] = view('forum.partial.draad_zijbalk', [
				'draden' => ForumDradenModel::instance()->getRecenteForumDraden((int)lid_instelling('zijbalk', 'forum'), $belangrijk),
				'aantalWacht' => ForumPostsModel::instance()->getAantalWachtOpGoedkeuring(),
				'belangrijk' => $belangrijk
			]);
		}
		// Zelfgeposte forumberichten
		if (lid_instelling('zijbalk', 'forum_zelf') > 0) {
			$posts = ForumPostsModel::instance()->getRecenteForumPostsVanLid(LoginModel::getUid(), (int)lid_instelling('zijbalk', 'forum_zelf'), true);
			$zijbalk[] = view('forum.partial.post_zijbalk', ['posts' => $posts]);
		}
		// Ledenmemory topscores
		if (LoginModel::mag(P_LEDEN_READ) AND lid_instelling('zijbalk', 'ledenmemory_topscores') > 0) {
			$lidjaar = LichtingenModel::getJongsteLidjaar();
			$lichting = LichtingenModel::get($lidjaar);
			$scores = LedenMemoryScoresModel::instance()->getGroepTopScores($lichting, (int)lid_instelling('zijbalk', 'ledenmemory_topscores'));
			$zijbalk[] = new LedenMemoryZijbalkView($scores, $lidjaar);
		}
		// Nieuwste fotoalbum
		if (lid_instelling('zijbalk', 'fotoalbum') == 'ja') {
			$album = FotoAlbumModel::instance()->getMostRecentFotoAlbum();
			if ($album !== null) {
				$zijbalk[] = new FotoAlbumZijbalkView($album);
			}
		}
		// Komende verjaardagen
		if (LoginModel::mag(P_LOGGED_IN) AND lid_instelling('zijbalk', 'verjaardagen') > 0) {
			$zijbalk[] = view('verjaardagen.komende', [
				'verjaardagen' => VerjaardagenModel::getKomende((int)lid_instelling('zijbalk', 'verjaardagen')),
				'toonpasfotos' => lid_instelling('zijbalk', 'verjaardagen_pasfotos') == 'ja',
			]);
		}
		return $zijbalk;
	}

}
