<?php

/**
 * MenuBeheerView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle menus en menu-items om te beheren.
 * 
 */
class MenuBeheerView extends TemplateView {

	private $menus;
	private $tree;

	public function __construct(MenuModel $model, $menu) {
		parent::__construct($model);
		$this->menus = $model->getAlleMenus();
		$items = $model->getMenuItems($menu, false);
		$this->tree = $model->getMenuTree($menu, $items);
	}

	public function getTitel() {
		if ($this->tree !== null && $this->tree->getMenu() !== '') {
			return 'Beheer ' . $this->tree->getMenu() . '-menu';
		}
		return 'Menubeheer';
	}

	public function view() {
		$this->assign('melding', $this->getMelding());
		$this->assign('kop', $this->getTitel());
		$this->assign('menus', $this->menus);
		$this->assign('root', $this->tree);
		$this->display('MVC/menu/beheer/menu_tree.tpl');
	}

}
