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
		if (LidInstellingen::get('zijbalk', 'favorieten') == 'ja') {
			array_unshift($zijbalk, new BlockMenuView(MenuModel::instance()->getMenu(LoginModel::getUid())));
		}
		// Is het al...
		if (LidInstellingen::get('zijbalk', 'ishetal') != 'niet weergeven') {
			require_once 'ishetalcontent.class.php';
			array_unshift($zijbalk, new IsHetAlContent(LidInstellingen::get('zijbalk', 'ishetal')));
		}
		// Agenda
		if (LoginModel::mag('P_AGENDA_READ') && LidInstellingen::get('zijbalk', 'agendaweken') > 0 && LidInstellingen::get('zijbalk', 'agenda_max') > 0) {
			require_once 'MVC/model/AgendaModel.class.php';
			$zijbalk[] = new AgendaZijbalkView(AgendaModel::instance(), LidInstellingen::get('zijbalk', 'agendaweken'));
		}
		// Laatste mededelingen
		if (LidInstellingen::get('zijbalk', 'mededelingen') > 0) {
			require_once 'mededelingen/mededeling.class.php';
			require_once 'mededelingen/mededelingencontent.class.php';
			$zijbalk[] = new MededelingenZijbalkContent((int) LidInstellingen::get('zijbalk', 'mededelingen'));
		}
		// Nieuwste belangrijke forumberichten
		if (LidInstellingen::get('zijbalk', 'forum_belangrijk') > 0) {
			require_once 'MVC/model/ForumModel.class.php';
			require_once 'MVC/view/ForumView.class.php';
			$zijbalk[] = new ForumDraadZijbalkView(
					ForumDradenModel::instance()->getRecenteForumDraden(
							(int) LidInstellingen::get('zijbalk', 'forum_belangrijk'), true), true);
		}
		// Nieuwste forumberichten
		if (LidInstellingen::get('zijbalk', 'forum') > 0) {
			require_once 'MVC/model/ForumModel.class.php';
			require_once 'MVC/view/ForumView.class.php';
			$belangrijk = (LidInstellingen::get('zijbalk', 'forum_belangrijk') > 0 ? false : null);
			$zijbalk[] = new ForumDraadZijbalkView(
					ForumDradenModel::instance()->getRecenteForumDraden(
							(int) LidInstellingen::get('zijbalk', 'forum'), $belangrijk), $belangrijk);
		}
		// Zelfgeposte forumberichten
		if (LidInstellingen::get('zijbalk', 'forum_zelf') > 0) {
			require_once 'MVC/model/ForumModel.class.php';
			require_once 'MVC/view/ForumView.class.php';
			$posts_draden = ForumPostsModel::instance()->getRecenteForumPostsVanLid(LoginModel::getUid(), LidInstellingen::get('zijbalk', 'forum_zelf'), true);
			$zijbalk[] = new ForumPostZijbalkView($posts_draden[0], $posts_draden[1]);
		}
		// Nieuwste fotoalbum
		if (LidInstellingen::get('zijbalk', 'fotoalbum') == 'ja') {
			require_once 'MVC/controller/FotoAlbumController.class.php';
			$album = FotoAlbumModel::instance()->getMostRecentFotoAlbum();
			if ($album !== null) {
				$zijbalk[] = new FotoAlbumZijbalkView($album);
			}
		}
		// Komende verjaardagen
		if (LidInstellingen::get('zijbalk', 'verjaardagen') > 0) {
			require_once 'lid/verjaardagcontent.class.php';
			$zijbalk[] = new VerjaardagContent('komende');
		}
		return $zijbalk;
	}

}
