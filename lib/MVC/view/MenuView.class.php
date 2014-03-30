<?php

/**
 * MenuView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van een menu waarbij afhankelijk van
 * de rechten van de gebruiker menu items wel
 * of niet worden getoond.
 */
abstract class MenuView extends TemplateView {

	public function __construct(MenuItem $tree_root) {
		parent::__construct($tree_root);
		$this->smarty->assign('root', $this->model);

		$req = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
		$this->smarty->assign('path', $req);
	}

}

class MainMenuView extends MenuView {

	public function view() {
		// SocCie-saldi & MaalCie-saldi
		$this->smarty->assign('saldi', LoginLid::instance()->getLid()->getSaldi());

		if (Loginlid::instance()->hasPermission('P_ADMIN')) {
			require_once 'savedquery.class.php';
			$this->smarty->assign('queues', array(
				'forum' => new SavedQuery(ROWID_QUEUE_FORUM),
				'meded' => new SavedQuery(ROWID_QUEUE_MEDEDELINGEN)
			));
		}
		$this->smarty->display('MVC/menu/main.tpl');
	}

}

class PageMenuView extends MenuView {

	public function view() {
		$this->smarty->display('MVC/menu/page.tpl');
	}

}

class BlockMenuView extends MenuView {

	public function view() {
		$this->smarty->display('MVC/menu/block.tpl');
	}

}
