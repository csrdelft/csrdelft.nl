<?php
require_once 'aclcontroller.class.php';
require_once 'menu/beheer/MenusModel.class.php';
require_once 'menu/beheer/BeheerMenusView.class.php';

/**
 * BeheerMenuController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class BeheerMenusController extends \ACLController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'beheer' => 'P_ADMIN'
			);
		}
		else {
			$this->acl = array(
				'nieuw' => 'P_ADMIN',
				'wijzig' => 'P_ADMIN',
				'verwijder' => 'P_ADMIN',
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$params = null;
		if ($this->hasParam(3)) {
			if ($this->action === 'beheer') {
				$params = $this->getParam(3);
			}
			else {
				$params = intval($this->getParam(3));
				if ($this->hasParam(4)) {
					$params = array('mid' => $params, 'prop' => $this->getParam(4));
				}
			}
		}
		$this->performAction($params);
	}
	
	public function action_beheer($menu=null) {
		$menus = MenusModel::getAlleMenus();
		$tree = MenusModel::getMenuTree($menu);
		$this->content = new BeheerMenusView($menus, $tree);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('menubeheer.css');
		$this->content->addScript('menubeheer.js');
	}
	
	public function action_nieuw($pid) {
		$prio = (int) filter_input(INPUT_POST, 'Prioriteit', FILTER_SANITIZE_NUMBER_INT);
		$text = filter_input(INPUT_POST, 'Tekst', FILTER_SANITIZE_STRING);
		$link = filter_input(INPUT_POST, 'Link', FILTER_SANITIZE_URL);
		$perm = filter_input(INPUT_POST, 'Permission', FILTER_SANITIZE_STRING);
		$show = (boolean) filter_input(INPUT_POST, 'Zichtbaar', FILTER_SANITIZE_STRING);
		$menu = filter_input(INPUT_POST, 'Menu', FILTER_SANITIZE_STRING);
		$item = MenusModel::newMenuItem($pid, $prio, $text, $link, $perm, $show, $menu);
		$this->content = new BeheerMenusView($item);
	}
	
	public function action_wijzig($mid, $prop) {
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
		$this->content = new BeheerMenusView($item);
	}
	
	public function action_verwijder($mid) {
		MenusModel::deleteMenuItem($mid);
		$this->content = new BeheerMenusView($mid);
	}
}

?>