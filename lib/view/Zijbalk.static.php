<?php

namespace CsrDelft\view;

use CsrDelft\model\agenda\AgendaModel;
use CsrDelft\model\forum\ForumDradenModel;
use CsrDelft\model\forum\ForumPostsModel;
use CsrDelft\model\fotoalbum\FotoAlbumModel;
use CsrDelft\model\groepen\LichtingenModel;
use CsrDelft\model\LedenMemoryScoresModel;
use CsrDelft\model\LidInstellingenModel;
use CsrDelft\model\MenuModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\model\VerjaardagenModel;
use CsrDelft\view\agenda\AgendaZijbalkView;
use CsrDelft\view\fotoalbum\FotoAlbumZijbalkView;
use CsrDelft\view\ledenmemory\LedenMemoryZijbalkView;
use CsrDelft\view\mededelingen\MededelingenZijbalkView;
use CsrDelft\view\menu\BlockMenuView;

/**
 * Zijbalk.static.php
 *
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 *
 */
abstract class Zijbalk {

	public static function addStandaardZijbalk(array $zijbalk) {
		// Favorieten menu
		if (LoginModel::mag(P_LOGGED_IN) AND LidInstellingenModel::get('zijbalk', 'favorieten') == 'ja') {
			$menu = MenuModel::instance()->getMenu(LoginModel::getUid());
			$menu->tekst = 'Favorieten';
			array_unshift($zijbalk, new BlockMenuView($menu));
		}
		// Is het al...
		if (LidInstellingenModel::get('zijbalk', 'ishetal') != 'niet weergeven') {
			array_unshift($zijbalk, new IsHetAlView(LidInstellingenModel::get('zijbalk', 'ishetal')));
		}

		// Sponsors
		if (LoginModel::mag(P_LOGGED_IN)) {
			$sponsor_menu = MenuModel::instance()->getMenu("sponsors");
			$sponsor_menu->tekst = 'Mogelijkheden';
			$zijbalk[] = new BlockMenuView($sponsor_menu);
		}

		// Agenda
		if (LoginModel::mag(P_AGENDA_READ) && LidInstellingenModel::get('zijbalk', 'agendaweken') > 0 && LidInstellingenModel::get('zijbalk', 'agenda_max') > 0) {
			$zijbalk[] = new AgendaZijbalkView(AgendaModel::instance(), LidInstellingenModel::get('zijbalk', 'agendaweken'));
		}
		// Laatste mededelingen
		if (LidInstellingenModel::get('zijbalk', 'mededelingen') > 0) {
			$zijbalk[] = new MededelingenZijbalkView((int)LidInstellingenModel::get('zijbalk', 'mededelingen'));
		}
		// Nieuwste belangrijke forumberichten
		if (LidInstellingenModel::get('zijbalk', 'forum_belangrijk') > 0) {
			$zijbalk[] = view('forum.partial.draad_zijbalk', [
				'draden' => ForumDradenModel::instance()->getRecenteForumDraden((int)LidInstellingenModel::get('zijbalk', 'forum_belangrijk'), true),
				'aantalWacht' => ForumPostsModel::instance()->getAantalWachtOpGoedkeuring(),
				'belangrijk' => true
			]);
		}
		// Nieuwste forumberichten
		if (LidInstellingenModel::get('zijbalk', 'forum') > 0) {
			$belangrijk = (LidInstellingenModel::get('zijbalk', 'forum_belangrijk') > 0 ? false : null);
			$zijbalk[] = view('forum.partial.draad_zijbalk', [
				'draden' => ForumDradenModel::instance()->getRecenteForumDraden((int)LidInstellingenModel::get('zijbalk', 'forum'), $belangrijk),
				'aantalWacht' => ForumPostsModel::instance()->getAantalWachtOpGoedkeuring(),
				'belangrijk' => $belangrijk
			]);
		}
		// Zelfgeposte forumberichten
		if (LidInstellingenModel::get('zijbalk', 'forum_zelf') > 0) {
			$posts = ForumPostsModel::instance()->getRecenteForumPostsVanLid(LoginModel::getUid(), (int)LidInstellingenModel::get('zijbalk', 'forum_zelf'), true);
			$zijbalk[] = view('forum.partial.post_zijbalk', ['posts' => $posts]);
		}
		// Ledenmemory topscores
		if (LoginModel::mag(P_LEDEN_READ) AND LidInstellingenModel::get('zijbalk', 'ledenmemory_topscores') > 0) {
			$lidjaar = LichtingenModel::getJongsteLidjaar();
			$lichting = LichtingenModel::get($lidjaar);
			$scores = LedenMemoryScoresModel::instance()->getGroepTopScores($lichting, (int)LidInstellingenModel::get('zijbalk', 'ledenmemory_topscores'));
			$zijbalk[] = new LedenMemoryZijbalkView($scores, $lidjaar);
		}
		// Nieuwste fotoalbum
		if (LidInstellingenModel::get('zijbalk', 'fotoalbum') == 'ja') {
			$album = FotoAlbumModel::instance()->getMostRecentFotoAlbum();
			if ($album !== null) {
				$zijbalk[] = new FotoAlbumZijbalkView($album);
			}
		}
		// Komende verjaardagen
		if (LoginModel::mag(P_LOGGED_IN) AND LidInstellingenModel::get('zijbalk', 'verjaardagen') > 0) {
			$zijbalk[] = new KomendeVerjaardagenView(
				VerjaardagenModel::getKomende((int)LidInstellingenModel::get('zijbalk', 'verjaardagen')),
				LidInstellingenModel::get('zijbalk', 'verjaardagen_pasfotos') == 'ja');
		}
		return $zijbalk;
	}

}
