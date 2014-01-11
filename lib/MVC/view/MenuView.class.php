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
	 * Unique short name of the menu
	 * @var string
	 */
	private $menu;
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

	public function __construct($menu) {
		parent::__construct();
		$this->menu = $menu;

		$path = $_SERVER['REQUEST_URI'];
		//$path = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL); // faalt op productie

		$items = MenuModel::getMenuItemsVoorLid($menu);
		foreach ($items as $item) {

			if (startsWith($path, $item->getLink())) {
				$this->active_item = $item;
			}
		}
		if ($this->active_item === null) {
			$this->active_item = new MenuItem();
		}

		$this->tree_root = MenuModel::getMenuTree($menu, $items);
	}

	/**
	 * 0: main
	 * 1: sub
	 * 2: page
	 * 3: block
	 * @param int $level
	 */
	public function view($level) {
		$this->assign('root', $this->tree_root);
		$this->assign('huidig', $this->active_item);

		if ($level === 0) {
			// SocCie-saldi & MaalCie-saldi
			$this->assign('saldi', LoginLid::instance()->getLid()->getSaldi());

			if (Loginlid::instance()->hasPermission('P_ADMIN')) {
				require_once 'savedquery.class.php';
				$this->assign('queues', array(
					'forum' => new SavedQuery(ROWID_QUEUE_FORUM),
					'meded' => new SavedQuery(ROWID_QUEUE_MEDEDELINGEN)
				));
			}
			$this->display('menu/menu.tpl');
		} elseif ($level === 3) {
			$this->display('menu/menu_block.tpl');
		}
	}

}

?>