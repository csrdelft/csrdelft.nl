<?php
require_once 'menu/beheer/MenuModel.class.php';
require_once 'menu/beheer/BeheerMenuView.class.php';

/**
 * BeheerMenuController.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 */
class BeheerMenuController extends \ACLController {

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
		$miid = null;
		if ($this->hasParam(3)) {
			if ($this->action === 'wijzig' || $this->action === 'verwijder') {
				$miid = intval($this->getParam(3));
				if ($this->hasParam(4)) {
					$miid = array('miid' => $miid, 'prop' => $this->getParam(4));
				}
			}
			else {
				$miid = $this->getParam(3);
			}
		}
		$this->performAction($miid);
	}
	
	public function action_beheer($menu=null) {
		$menus = MenuModel::getAlleMenus();
		$tree = MenuModel::getMenuTree($menu);
		$this->content = new BeheerMenuView($menus, $tree);
		$this->content = new \csrdelft($this->getContent());
		$this->content->addStylesheet('js/autocomplete/jquery.autocomplete.css');
		$this->content->addScript('autocomplete/jquery.autocomplete.min.js');
	}
	
	public function action_nieuw() {
		$pid = (int) filter_input(INPUT_POST, 'ParentId', FILTER_SANITIZE_NUMBER_INT);
		$prio = (int) filter_input(INPUT_POST, 'Prioriteit', FILTER_SANITIZE_NUMBER_INT);
		$text = filter_input(INPUT_POST, 'Tekst', FILTER_SANITIZE_STRING);
		$link = filter_input(INPUT_POST, 'Link', FILTER_SANITIZE_URL);
		$perm = filter_input(INPUT_POST, 'Permission', FILTER_SANITIZE_STRING);
		$show = (boolean) filter_input(INPUT_POST, 'Zichtbaar', FILTER_SANITIZE_STRING);
		$menu = filter_input(INPUT_POST, 'Menu', FILTER_SANITIZE_STRING);
		$item = MenuModel::newMenuItem($pid, $prio, $text, $link, $perm, $show, $menu);
		$this->content = new BeheerMenuView($item);
	}
	
	public function action_wijzig($args) {
		$item = MenuModel::getMenuItem($args['miid']);
		$prop = ucfirst($args['prop']);
		$setter = 'set'. $prop;
		if (method_exists($item, $setter)) {
			$val = filter_input(INPUT_POST, $prop);
			if (is_int($val)) {
				$val = (int) $val;
			}
			else if (is_bool($val)) {
				$val = (boolean) $val;
			}
			$item->$setter($val);
			MenuModel::saveMenuItem($item);
		}
		else{
			throw new Exception('Wijzig faalt: '. $setter .' undefined');
		}
		$this->content = new BeheerMenuView($item);
	}
	
	public function action_verwijder($miid) {
		MenuModel::verwijderMenuItem($miid);
		$this->content = new BeheerMenuView($miid);
	}
}

?>