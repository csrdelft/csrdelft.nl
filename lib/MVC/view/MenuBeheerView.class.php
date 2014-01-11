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

	private $_menus;
	private $_root;

	public function __construct($menus, MenuItem $root = null) {
		parent::__construct();
		$this->_menus = $menus;
		$this->_root = $root;
	}

	public function getTitel() {
		if ($this->_root !== null && $this->_root->getMenu() !== '') {
			return 'Beheer ' . $this->_root->getMenu() . '-menu';
		}
		return 'Menubeheer';
	}

	public function view() {
		if (is_array($this->_menus)) {
			$this->assign('melding', $this->getMelding());
			$this->assign('kop', $this->getTitel());
			$this->assign('menus', $this->_menus);
			$this->assign('root', $this->_root);
			$this->display('menu/beheer/menu_tree.tpl');
		} elseif (is_int($this->_menus)) {
			echo '<div id="menu-item-' . $this->_menus . '" class="remove"></div>';
		} else {
			$this->assign('item', $this->_menus);
			$this->display('menu/beheer/menu_item.tpl');
		}
	}

}

?>