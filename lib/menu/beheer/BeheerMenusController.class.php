<?php
require_once 'MVC/controller/AclController.abstract.php';
require_once 'menu/beheer/MenusModel.class.php';
require_once 'menu/beheer/BeheerMenusView.class.php';

/**
 * BeheerMenuController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class BeheerMenusController extends AclController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'beheer' => 'P_ADMIN',
				'verwijder' => 'P_ADMIN'
			);
		}
		else {
			$this->acl = array(
				'nieuw' => 'P_ADMIN',
				'wijzig' => 'P_ADMIN'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(0)) {
			$this->action = $this->getParam(0);
		}
		$params = array();
		if ($this->hasParam(1)) {
			if ($this->action === 'beheer') {
				$params[] = $this->getParam(1);
			}
			else {
				$params[] = intval($this->getParam(1));
				if ($this->hasParam(2)) {
					$params[] = $this->getParam(2);
				}
			}
		}
		$this->performAction($params);
	}
	
	public function beheer($menu=null) {
		$menus = MenusModel::getAlleMenus();
		if ($menu === null) {
			$menu = '';
		}
		$items = MenusModel::getMenuItems($menu, false);
		$tree = MenusModel::getMenuTree($menu, $items);
		$this->content = new BeheerMenusView($menus, $tree);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('menubeheer.css');
		$this->content->addScript('menubeheer.js');
	}
	
	public function nieuw($pid) {
		$prio = (int) filter_input(INPUT_POST, 'Prioriteit', FILTER_SANITIZE_NUMBER_INT);
		$text = filter_input(INPUT_POST, 'Tekst', FILTER_SANITIZE_STRING);
		$link = filter_input(INPUT_POST, 'Link', FILTER_SANITIZE_URL);
		$perm = filter_input(INPUT_POST, 'Permission', FILTER_SANITIZE_STRING);
		$show = (boolean) filter_input(INPUT_POST, 'Zichtbaar', FILTER_SANITIZE_STRING);
		$menu = filter_input(INPUT_POST, 'Menu', FILTER_SANITIZE_STRING);
		$item = MenusModel::newMenuItem($pid, $prio, $text, $link, $perm, $show, $menu);
		\SimpleHTML::invokeRefresh('/menubeheer/beheer/'. $item->getMenu(), $item->getTekst() .' ('. $item->getMenuId() .') aangemaakt', 1);
	}
	
	public function wijzig($mid, $prop) {
		$item = MenusModel::getMenuItem($mid);
		$prop = ucfirst($prop);
		$setter = 'set'. $prop;
		if (method_exists($item, $setter)) {
			$val = filter_input(INPUT_POST, $prop);
			$item->$setter($val);
			MenusModel::updateMenuItem($item);
		}
		else{
			throw new \Exception('Wijzig faalt: '. $setter .' undefined');
		}
		\SimpleHTML::invokeRefresh('/menubeheer/beheer/'. $item->getMenu(), $item->getTekst() .' ('. $item->getMenuId() .') opgeslagen', 1);
	}
	
	public function verwijder($mid) {
		$item = MenusModel::getMenuItem($mid);
		MenusModel::deleteMenuItem($item);
		\SimpleHTML::invokeRefresh('/menubeheer/beheer/'. $item->getMenu(), $item->getTekst() .' ('. $item->getMenuId() .') verwijderd', 1);
	}
}

?>