<?php

namespace CsrDelft\view\menu;

class PageMenuView extends MenuView {

	public function view() {
		parent::view();
		$this->smarty->display('menu/page.tpl');
	}

}
