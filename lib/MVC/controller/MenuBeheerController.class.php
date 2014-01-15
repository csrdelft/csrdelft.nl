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

	/**
	 * Data access model
	 * @var MenuModel
	 */
	private $model;

	public function __construct($query) {
		parent::__construct($query);
		$this->model = new MenuModel();
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
		$this->performAction($this->getParams(1));
	}

	public function beheer($menu = '') {
		$this->view = new MenuBeheerView($this->model, $menu);
		$this->view = new csrdelft($this->getContent());
		$this->view->addStylesheet('menubeheer.css');
		$this->view->addScript('menubeheer.js');
	}

	public function verwijder($id) {
		try {
			$item = $this->model->deleteMenuItem($id);
			invokeRefresh('/menubeheer/beheer/' . $item->menu_naam, 'Verwijderd ' . $item->tekst . ' (' . $item->item_id . ')', 1);
		} catch (Exception $e) {
			invokeRefresh('/menubeheer/beheer', $e->getMessage(), -1);
		}
	}

	public function nieuw($parent_id) {
		$item = new MenuItem();
		$item->parent_id = (int) $parent_id;
		$item->prioriteit = (int) filter_input(INPUT_POST, 'prioriteit', FILTER_SANITIZE_NUMBER_INT);
		$item->tekst = filter_input(INPUT_POST, 'tekst', FILTER_SANITIZE_STRING);
		$item->link = filter_input(INPUT_POST, 'link', FILTER_SANITIZE_URL);
		$item->permission = filter_input(INPUT_POST, 'permission', FILTER_SANITIZE_STRING);
		$item->zichtbaar = (boolean) filter_input(INPUT_POST, 'zichtbaar', FILTER_SANITIZE_STRING);
		$item->menu_naam = filter_input(INPUT_POST, 'menu_naam', FILTER_SANITIZE_STRING);
		$model = new MenuModel();
		$model->saveMenuItem($item);
		invokeRefresh('/menubeheer/beheer/' . $item->menu_naam, 'Nieuw aangemaakt ' . $item->tekst . ' (' . $item->id . ')', 1);
	}

	public function wijzig($id, $property) {
		$value = filter_input(INPUT_POST, $property);
		$model = new MenuModel();
		try {
			$item = $model->wijzigProperty($id, $property, $value);
			invokeRefresh('/menubeheer/beheer/' . $item->menu_naam, 'Wijzigingen opgeslagen ' . $item->item_id . ': ' . $item->tekst, 1);
		} catch (Exception $e) {
			invokeRefresh('/menubeheer/beheer', $e->getMessage(), -1);
		}
	}

}
