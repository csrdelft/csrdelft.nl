<?php

require_once 'MVC/model/MenuModel.class.php';

/**
 * MenuView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van een menu waarbij afhankelijk van
 * de rechten van de gebruiker menu items wel
 * of niet worden getoond.
 */
class MenuView extends TemplateView {

	/**
	 * 0: main
	 * 1: sub
	 * 2: page
	 * 3: block
	 * @var int
	 */
	private $level;
	/**
	 * Root MenuItem of menu tree
	 * @var MenuItem
	 */
	private $tree_root;
	/**
	 * MenuItem of the current page
	 * @var MenuItem
	 */
	private $active_item;

	public function __construct($menu_name, $level) {
		parent::__construct(new MenuModel());
		$this->level = $level;
		$this->tree_root = $this->model->getMenuTree($menu_name);
		if ($this->active_item === null) {
			$this->active_item = new MenuItem();
		}
	}

	public function view() {
		$this->smarty->assign('root', $this->tree_root);
		$this->smarty->assign('path', filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL));

		if ($this->level === 0) {
			// SocCie-saldi & MaalCie-saldi
			$this->smarty->assign('saldi', LoginLid::instance()->getLid()->getSaldi());

			if (Loginlid::instance()->hasPermission('P_ADMIN')) {
				require_once 'savedquery.class.php';
				$this->smarty->assign('queues', array(
					'forum' => new SavedQuery(ROWID_QUEUE_FORUM),
					'meded' => new SavedQuery(ROWID_QUEUE_MEDEDELINGEN)
				));
			}
			$this->smarty->display('MVC/menu/menu.tpl');
		} elseif ($this->level === 3) {
			$this->smarty->display('MVC/menu/menu_block.tpl');
		}
	}

}
