<?php

/**
 * simplehtml.class.php
 * 
 * @deprecated
 * 
 * @author C.S.R. Delft <pubcie@csrdelft.nl>
 * 
 */
abstract class SimpleHTML implements View {

	/**
	 * Stores a message.
	 *
	 * Levels can be:
	 *
	 * -1 error
	 *  0 info
	 *  1 success
	 *  2 notify
	 *
	 * @see    SimpleHTML::getMelding()
	 * gebaseerd op DokuWiki code
	 */
	public static function setMelding($msg, $lvl) {
		$errors[-1] = 'error';
		$errors[0] = 'info';
		$errors[1] = 'success';
		$errors[2] = 'notify';
		$msg = trim($msg);
		if (!empty($msg) AND ( $lvl === -1 OR $lvl === 0 OR $lvl === 1 OR $lvl === 2 )) {
			if (!isset($_SESSION['melding'])) {
				$_SESSION['melding'] = array();
			}
			// gooit verouderde gegevens weg
			if (is_string($_SESSION['melding'])) {
				$_SESSION['melding'] = array();
			}
			$_SESSION['melding'][] = array('lvl' => $errors[$lvl], 'msg' => $msg);
		}
	}

	/**
	 * Geeft berichten weer die opgeslagen zijn in de sessie met met SimpleHTML::setMelding($msg, $lvl)
	 * 
	 * @return string html van melding(en) of lege string
	 */
	public static function getMelding() {
		if (isset($_SESSION['melding']) AND is_array($_SESSION['melding'])) {
			$sMelding = '<div id="melding">';
			$shown = array();
			foreach ($_SESSION['melding'] as $msg) {
				$hash = md5($msg['msg']);
				//if (isset($shown[$hash]))
				//	continue; // skip double messages
				$sMelding .= '<div class="msg' . $msg['lvl'] . '">';
				$sMelding .= $msg['msg'];
				$sMelding .= '</div>';
				$shown[$hash] = 1;
			}
			$sMelding .= '</div>';
			// maar één keer tonen, de melding.
			unset($_SESSION['melding']);
			return $sMelding;
		} else {
			return '';
		}
	}

	public static function getStandaardZijbalk() {
		$zijbalk = array();
		// Is het al...
		if (LidInstellingen::get('zijbalk', 'ishetal') != 'niet weergeven') {
			require_once 'ishetalcontent.class.php';
			$zijbalk[] = new IsHetAlContent(LidInstellingen::get('zijbalk', 'ishetal'));
		}
		// Agenda
		if (LoginModel::mag('P_AGENDA_READ') && LidInstellingen::get('zijbalk', 'agendaweken') > 0) {
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
			$album = FotoAlbumModel::getMostRecentFotoAlbum();
			if ($album !== null) {
				$zijbalk[] = new FotoAlbumZijbalkView($album);
			}
		}
		// Komende verjaardagen
		if (LidInstellingen::get('zijbalk', 'verjaardagen') > 0) {
			require_once 'lid/verjaardagcontent.class.php';
			$zijbalk[] = new VerjaardagContent('komende');
		}
		// Quick navigation buttons
		if (LidInstellingen::get('layout', 'zijbalk') == 'fixeer') {
			$zijbalk[] = new QuickNavigateMenuView();
		}
		return $zijbalk;
	}

	public static function getDebug($sql = true, $get = true, $post = true, $files = true, $cookie = true, $session = true) {
		$debug = '';
		if ($sql) {
			$debug .= '<hr />SQL<hr />';
			$debug .= '<pre>' . mb_htmlentities(print_r(array("PDO" => Database::getQueries(), "MySql" => MijnSqli::instance()->getQueries()), true)) . '</pre>';
		}
		if ($get) {
			$debug .= '<hr />GET<hr />';
			if (count($_GET) > 0) {
				$debug .= '<pre>' . mb_htmlentities(print_r($_GET, true)) . '</pre>';
			}
		}
		if ($post) {
			$debug .= '<hr />POST<hr />';
			if (count($_POST) > 0) {
				$debug .= '<pre>' . mb_htmlentities(print_r($_POST, true)) . '</pre>';
			}
		}
		if ($files) {
			$debug .= '<hr />FILES<hr />';
			if (count($_FILES) > 0) {
				$debug .= '<pre>' . mb_htmlentities(print_r($_FILES, true)) . '</pre>';
			}
		}
		if ($cookie) {
			$debug .= '<hr />COOKIE<hr />';
			if (count($_COOKIE) > 0) {
				$debug .= '<pre>' . mb_htmlentities(print_r($_COOKIE, true)) . '</pre>';
			}
		}
		if ($session) {
			$debug .= '<hr />SESSION<hr />';
			if (count($_SESSION) > 0) {
				$debug .= '<pre>' . mb_htmlentities(print_r($_SESSION, true)) . '</pre>';
			}
		}
		return $debug;
	}

}
