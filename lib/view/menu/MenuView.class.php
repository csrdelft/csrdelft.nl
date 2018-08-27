<?php

namespace CsrDelft\view\menu;

use CsrDelft\model\entity\MenuItem;
use CsrDelft\view\SmartyTemplateView;

/**
 * MenuView.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van een menu waarbij afhankelijk van
 * de rechten van de gebruiker menu items wel
 * of niet worden getoond.
 */
abstract class MenuView extends SmartyTemplateView {

	public function __construct(MenuItem $tree_root) {
		parent::__construct($tree_root);
	}

	public function view() {
		$this->smarty->assign('root', $this->model);
	}

}
