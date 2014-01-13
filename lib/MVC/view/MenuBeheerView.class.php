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

	/**
	 * List of all menus
	 * @var array
	 */
	private $menus;
	/**
	 * Root of the menu tree
	 * @var MenuItem
	 */
	private $tree_root;

	public function __construct($menu) {
		parent::__construct(new MenuModel());
		$this->menus = $this->model->getAlleMenus();
		$items = $this->model->getMenuItems($menu);
		$this->tree_root = $this->model->buildMenuTree($menu, $items);
	}

	public function getTitel() {
		if ($this->tree_root !== null && $this->tree_root->menu_naam !== '') {
			return 'Beheer ' . $this->tree_root->menu_naam . '-menu';
		}
		return 'Menubeheer';
	}

	public function view() {
		$this->assign('melding', $this->getMelding());
		$this->assign('kop', $this->getTitel());
		$this->assign('menus', $this->menus);
		$this->assign('root', $this->tree_root);
		$this->display('MVC/menu/beheer/menu_tree.tpl');
	}

}
