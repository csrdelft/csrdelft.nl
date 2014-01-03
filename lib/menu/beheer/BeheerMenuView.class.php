<?php
/**
 * BeheerMenuView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle menus en menu-items om te beheren.
 * 
 */
class BeheerMenuView extends \SimpleHtml {

	private $_menus;
	private $_root;
	
	public function __construct($menus, MenuItem $root=null) {
		$this->_menus = $menus;
		$this->_root = $root;
	}
	
	public function getTitel() {
		if ($this->_root !== null) {
			return 'Beheer '. $this->_root->getMenu() .'-menu';
		}
		return 'Menubeheer';
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		
		if (is_array($this->_menus)) {
			$smarty->assign('kop', $this->getTitel());
			$smarty->assign('menus', $this->_menus);
			$smarty->assign('tree', $this->_root);
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