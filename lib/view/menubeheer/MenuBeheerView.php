<?php

namespace CsrDelft\view\menubeheer;

use CsrDelft\model\entity\MenuItem;
use CsrDelft\model\MenuModel;
use CsrDelft\view\SmartyTemplateView;

/**
 * MenuBeheerView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van alle menus en menu-items om te beheren.
 *
 */
class MenuBeheerView extends SmartyTemplateView {

	public function __construct(MenuItem $tree_root) {
		parent::__construct($tree_root, 'Menubeheer');
	}

	public function view() {
		$this->smarty->assign('root', $this->model);
		$this->smarty->assign('menus', MenuModel::instance()->getMenuBeheerLijst());
		$this->smarty->display('menu/beheer/menu_tree.tpl');
	}

}
