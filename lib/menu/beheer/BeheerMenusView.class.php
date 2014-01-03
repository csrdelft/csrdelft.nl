<?php
/**
 * BeheerMenuView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle menus en menu-items om te beheren.
 * 
 */
class BeheerMenusView extends \SimpleHtml {

	private $_menus;
	private $_tree;
	
	public function __construct($menus, $tree=null) {
		$this->_menus = $menus;
		$this->_tree = $tree;
	}
	
	public function getTitel() {
		if ($this->_tree !== null) {
			return 'Beheer '. $this->_tree->getMenu() .'-menu';
		}
		return 'Menubeheer';
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		
		if (is_array($this->_menus)) {
			$smarty->assign('melding', $this->getMelding());
			$smarty->assign('kop', $this->getTitel());
			$smarty->assign('menus', $this->_menus);
			$smarty->assign('tree', $this->_tree);
			$smarty->display('menu/beheer/menu_tree.tpl');
		}
		elseif (is_int($this->_menus)) {
			echo '<div id="menu-item-'. $this->_menus .'" class="remove"></div>';
		}
		else {
			$smarty->assign('item', $this->_menus);
			$smarty->display('menu/beheer/menu_item.tpl');
		}
	}
}

?>