<?php

/**
 * Zijbalk.static.php
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * 
 */
abstract class Zijbalk {

	public static function addStandaardZijbalk(array $zijbalk) {
		// Favorieten menu
		if (LoginModel::mag('P_LOGGED_IN') AND LidInstellingen::get('zijbalk', 'favorieten') == 'ja') {
			$menu = MenuModel::instance()->getMenu(LoginModel::getUid());
			$menu->tekst = 'Favorieten';
			array_unshift($zijbalk, new BlockMenuView($menu));
		}
		// Is het al...
		if (LidInstellingen::get('zijbalk', 'ishetal') != 'niet weergeven') {
			require_once 'view/IsHetAlView.class.php';
			array_unshift($zijbalk, new IsHetAlView(LidInstellingen::get('zijbalk', 'ishetal')));
		}

		// Sponsors
		if (LoginModel::mag('P_LOGGED_IN')) {
			$sponsor_menu = MenuModel::instance()->getMenu("sponsors");
			$sponsor_menu->tekst = 'Sponsors';
			$zijbalk[] = new BlockMenuView($sponsor_menu);
		}

		// Agenda
		if (LoginModel::mag('P_AGENDA_READ') && LidInstellingen::get('zijbalk', 'agendaweken') > 0 && LidInstellingen::get('zijbalk', 'agenda_max') > 0) {
			require_once 'model/AgendaModel.class.php';
			$zijbalk[] = new AgendaZijbalkView(AgendaModel::instance(), LidInstellingen::get('zijbalk', 'agendaweken'));
		}
		// Laatste mededelingen
		if (LidInstellingen::get('zijbalk', 'mededelingen') > 0) {
			require_once 'model/mededelingen/MededelingenModel.class.php';
			require_once 'view/MededelingenView.class.php';
			$zijbalk[] = new MededelingenZijbalkView((int) LidInstellingen::get('zijbalk', 'mededelingen'));
		}
		// Nieuwste belangrijke forumberichten
		if (LidInstellingen::get('zijbalk', 'forum_belangrijk') > 0) {
			require_once 'model/ForumModel.class.php';
			require_once 'view/ForumView.class.php';
			$zijbalk[] = new ForumDraadZijbalkView(
					ForumDradenModel::instance()->getRecenteForumDraden(
							(int) LidInstellingen::get('zijbalk', 'forum_belangrijk'), true), true);
		}
		// Nieuwste forumberichten
		if (LidInstellingen::get('zijbalk', 'forum') > 0) {
			require_once 'model/ForumModel.class.php';
			require_once 'view/ForumView.class.php';
			$belangrijk = (LidInstellingen::get('zijbalk', 'forum_belangrijk') > 0 ? false : null);
			$zijbalk[] = new ForumDraadZijbalkView(
					ForumDradenModel::instance()->getRecenteForumDraden(
							(int) LidInstellingen::get('zijbalk', 'forum'), $belangrijk), $belangrijk);
		}
		// Zelfgeposte forumberichten
		if (LidInstellingen::get('zijbalk', 'forum_zelf') > 0) {
			require_once 'model/ForumModel.class.php';
			require_once 'view/ForumView.class.php';
			$posts = ForumPostsModel::instance()->getRecenteForumPostsVanLid(LoginModel::getUid(), (int) LidInstellingen::get('zijbalk', 'forum_zelf'), true);
			$zijbalk[] = new ForumPostZijbalkView($posts);
		}
		// Ledenmemory topscores
		if (LoginModel::mag('P_LEDEN_READ') AND LidInstellingen::get('zijbalk', 'ledenmemory_topscores') == 'ja') {
			require_once 'model/LedenMemoryScoresModel.class.php';
			require_once 'view/LedenMemoryView.class.php';
			$lidjaar = LichtingenModel::getJongsteLidjaar();
			$lichting = LichtingenModel::get($lidjaar);
			$scores = LedenMemoryScoresModel::instance()->getGroepTopScores($lichting);
			$zijbalk[] = new LedenMemoryZijbalkView($scores, $lidjaar);
		}
		// Nieuwste fotoalbum
		if (LidInstellingen::get('zijbalk', 'fotoalbum') == 'ja') {
			require_once 'controller/FotoAlbumController.class.php';
			$album = FotoAlbumModel::instance()->getMostRecentFotoAlbum();
			if ($album !== null) {
				$zijbalk[] = new FotoAlbumZijbalkView($album);
			}
		}
		// Komende verjaardagen
		if (LoginModel::mag('P_LOGGED_IN') AND LidInstellingen::get('zijbalk', 'verjaardagen') > 0) {
			require_once 'view/VerjaardagenView.class.php';
			$zijbalk[] = new VerjaardagenView('komende');
		}
		return $zijbalk;
	}

}
