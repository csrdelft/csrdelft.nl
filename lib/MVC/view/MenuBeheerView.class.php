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

	public function __construct($menu) {
		parent::__construct();
		$model = new MenuModel();
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
		if (is_array($this->menus)) {
			$this->assign('melding', $this->getMelding());
			$this->assign('kop', $this->getTitel());
			$this->assign('menus', $this->menus);
			$this->assign('root', $this->tree);
			$this->display('menu/beheer/menu_tree.tpl');
		} elseif (is_int($this->menus)) {
			echo '<div id="menu-item-' . $this->menus . '" class="remove"></div>';
		} else {
			$this->assign('item', $this->menus);
			$this->display('menu/beheer/menu_item.tpl');
		}
	}

}

?>