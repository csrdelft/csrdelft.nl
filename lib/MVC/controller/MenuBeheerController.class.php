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
		if (!$this->isPosted()) {
			$this->acl = array(
				'beheer' => 'P_ADMIN'
			);
		} else {
			$this->acl = array(
				'toevoegen' => 'P_ADMIN',
				'bewerken' => 'P_ADMIN',
				'verwijderen' => 'P_ADMIN'
			);
		}
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		$this->performAction($this->getParams(3));
	}

	public function beheer($menu_naam = '') {
		$body = new MenuBeheerView($this->model, $menu_naam);
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('menubeheer.css');
	}

	public function toevoegen($parent_id) {
		$item = $this->model->newMenuItem($parent_id);
		$this->view = new MenuItemFormView($item, $this->action, $parent_id); // fetches POST values itself
		if ($this->view->validate()) {
			$id = $this->model->create($item);
			$item->item_id = (int) $id;
			//setMelding('Toegevoegd', 1);
			$this->view = new MenuItemView($item, $this->action);
		}
	}

	public function bewerken($aid) {
		$item = $this->model->getMenuItem($aid);
		$this->view = new MenuItemFormView($item, $this->action, $item->item_id); // fetches POST values itself
		if ($this->view->validate()) {
			$rowcount = $this->model->update($item);
			if ($rowcount > 0) {
				//setMelding('Bijgewerkt', 1);
			} else {
				//setMelding('Geen wijzigingen', 0);
			}
			$this->view = new MenuItemView($item, $this->action);
		}
	}

	public function verwijderen($id) {
		try {
			$item = $this->model->getMenuItem($id);
			$this->model->delete($item);
			setMelding('Verwijderd ' . $item->tekst . ' (' . $item->item_id . ')', 1);
		} catch (Exception $e) {
			setMelding($e->getMessage(), -1);
		}
		$this->view = new MenuItemView($item, $this->action);
	}

}
