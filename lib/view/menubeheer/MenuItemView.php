<?php

namespace CsrDelft\view\menubeheer;

use CsrDelft\model\entity\MenuItem;
use CsrDelft\view\SmartyTemplateView;

class MenuItemView extends SmartyTemplateView {

	public function __construct(MenuItem $item) {
		parent::__construct($item);
	}

	public function view() {
		$this->smarty->assign('item', $this->model);
		$this->smarty->display('menu/beheer/menu_item.tpl');
	}

}
