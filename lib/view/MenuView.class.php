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

class BlockMenuView extends MenuView {

	public function view() {
		parent::view();
		$this->smarty->display('menu/block.tpl');
	}

}

class PageMenuView extends MenuView {

	public function view() {
		parent::view();
		$this->smarty->display('menu/page.tpl');
	}

}

class MainMenuView extends MenuView {

	public function __construct() {
		parent::__construct(MenuModel::instance()->getMenu('main'));
	}

	public function view() {
		parent::view();
		require_once 'savedquery.class.php';
		$mcount = new SavedQuery(62);
		$this->smarty->assign('mcount', $mcount->count());
		$this->smarty->assign('fcount', ForumPostsModel::instance()->getAantalWachtOpGoedkeuring());
		$this->smarty->assign('favorieten', MenuModel::instance()->getMenu(LoginModel::getUid()));
		$this->smarty->assign('zoekbalk', new ZoekbalkView());
		$this->smarty->display('menu/main.tpl');
	}

}
