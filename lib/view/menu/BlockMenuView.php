<?php

namespace CsrDelft\view\menu;

class BlockMenuView extends MenuView {

	public function view() {
		parent::view();
		$this->smarty->display('menu/block.tpl');
	}

}
