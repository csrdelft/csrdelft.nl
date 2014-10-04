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
		foreach (MenuModel::instance()->find() as $item) {
			if ($item->zichtbaar AND $item->parent_id > 0 AND $item->magBekijken()) {
				$instantsearch[$item->tekst] = $item->link;
			}
		}
		require_once 'MVC/model/ForumModel.class.php';
		foreach (ForumDelenModel::instance()->getForumDelenVoorLid(false) as $deel) {
			if (!array_key_exists($deel->titel, $instantsearch)) {
				$instantsearch[$deel->titel] = '/forum/deel/' . $deel->forum_id;
			}
		}
		$this->smarty->assign('instantsearch', $instantsearch);
		$this->smarty->display('MVC/menu/main_menu.tpl');
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

class FavorietenMenuView extends SmartyTemplateView {

	public function __construct() {
		parent::__construct(null);
	}

	public function view() {
		//$this->smarty->display('MVC/menu/quicknav.tpl');
	}

}
