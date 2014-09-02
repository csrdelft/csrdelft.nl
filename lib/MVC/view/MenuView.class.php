<?php

/**
 * MenuView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van een menu waarbij afhankelijk van
 * de rechten van de gebruiker menu items wel
 * of niet worden getoond.
 */
abstract class MenuView extends SmartyTemplateView {

	public function __construct(MenuItem $tree_root) {
		parent::__construct($tree_root);
	}

	public function view() {
		$this->smarty->assign('root', $this->model);
	}

}

class MainMenuView extends MenuView {

	public function view() {
		parent::view();

		$instantsearch = array();
		foreach (ForumDradenModel::instance()->getRecenteForumDraden(null, null) as $draad) {
			$instantsearch[$draad->titel] = '/forum/onderwerp/' . $draad->draad_id;
		}
		foreach (ForumDelenModel::instance()->getForumDelenVoorLid(false) as $deel) {
			$instantsearch[$deel->titel] = '/forum/deel/' . $deel->forum_id;
		}
		foreach (MenuModel::instance()->find() as $item) {
			if ($item->magBekijken()) {
				$instantsearch[$item->tekst] = $item->link;
			}
		}
		$this->smarty->assign('instantsearch', $instantsearch);

		// SocCie-saldi & MaalCie-saldi
		$this->smarty->assign('saldi', LoginModel::instance()->getLid()->getSaldi());

		if (LoginModel::mag('P_ADMIN')) {

			require_once 'MVC/model/ForumModel.class.php';
			$this->smarty->assign('forumcount', ForumPostsModel::instance()->getAantalWachtOpGoedkeuring());

			require_once 'savedquery.class.php';
			$this->smarty->assign('queues', array(
				'meded' => new SavedQuery(62) //ROW ID QUEUE MEDEDELINGEN
			));
		}
		$this->smarty->display('MVC/menu/main.tpl');
	}

}

class PageMenuView extends MenuView {

	public function view() {
		parent::view();
		$this->smarty->display('MVC/menu/page.tpl');
	}

}

class BlockMenuView extends MenuView {

	public function view() {
		parent::view();
		$this->smarty->display('MVC/menu/block.tpl');
	}

}
