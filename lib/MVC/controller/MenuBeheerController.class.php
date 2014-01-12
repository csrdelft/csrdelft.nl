<?php

require_once 'MVC/model/MenuModel.class.php';
require_once 'MVC/view/MenuBeheerView.class.php';

/**
 * MenuBeheerController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class MenuBeheerController extends AclController {

	public function __construct($query) {
		parent::__construct($query);
		if (!parent::isPOSTed()) {
			$this->acl = array(
				'beheer' => 'P_ADMIN',
				'verwijder' => 'P_ADMIN'
			);
		} else {
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
			} else {
				$params[] = (int) $this->getParam(1);
				if ($this->hasParam(2)) {
					$params[] = $this->getParam(2);
				}
			}
		}
		$this->performAction($params);
	}

	public function beheer($menu = '') {
		$this->content = new MenuBeheerView($menu);
		$this->content = new csrdelft($this->getContent());
		$this->content->addStylesheet('menubeheer.css');
		$this->content->addScript('menubeheer.js');
	}

	public function verwijder($id) {
		$model = new MenuModel();
		$menuitem = $model->load($id);
		$model->deleteMenuItem($menuitem);
		SimpleHTML::invokeRefresh('/menubeheer/beheer/' . $menuitem->menu_naam, 'Verwijderd ' . $menuitem->tekst . ' (' . $menuitem->getMenuId() . ')', 1);
	}

	public function nieuw() {
		$item = new MenuItem();
		$item->prioriteit = (int) filter_input(INPUT_POST, 'prioriteit', FILTER_SANITIZE_NUMBER_INT);
		$item->tekst = filter_input(INPUT_POST, 'tekst', FILTER_SANITIZE_STRING);
		$item->link = filter_input(INPUT_POST, 'link', FILTER_SANITIZE_URL);
		$item->permission = filter_input(INPUT_POST, 'permission', FILTER_SANITIZE_STRING);
		$item->zichtbaar = (boolean) filter_input(INPUT_POST, 'zichtbaar', FILTER_SANITIZE_STRING);
		$item->menu_naam = filter_input(INPUT_POST, 'menu_naam', FILTER_SANITIZE_STRING);
		$model = new MenuModel();
		$model->saveMenuItem($item);
		SimpleHTML::invokeRefresh('/menubeheer/beheer/' . $item->menu_naam, 'Nieuw aangemaakt ' . $item->tekst . ' (' . $item->id . ')', 1);
	}

	public function wijzig($id, $property) {
		$value = filter_input(INPUT_POST, $property);
		$model = new MenuModel();
		$model->saveProperty($id, $property, $value);
		$menuitem = $model->load($id);
		SimpleHTML::invokeRefresh('/menubeheer/beheer/' . $item->menu_naam, 'Wijzigingen opgeslagen ' . $item->tekst . ' (' . $item->id . ')', 1);
	}

}

?>