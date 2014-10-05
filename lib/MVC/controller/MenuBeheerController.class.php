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
		parent::__construct($query, MenuModel::instance());
		if (!$this->isPosted()) {
			$this->acl = array(
				'beheer' => 'P_LOGGED_IN'
			);
		} else {
			$this->acl = array(
				'toevoegen'		 => 'P_LOGGED_IN',
				'bewerken'		 => 'P_LOGGED_IN',
				'verwijderen'	 => 'P_LOGGED_IN',
				'zichtbaar'		 => 'P_LOGGED_IN'
			);
		}
	}

	public function performAction(array $args = array()) {
		$this->action = 'beheer';
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function beheer($menu_name) {
		if ($menu_name != LoginModel::getUid() AND ! LoginModel::mag('P_ADMIN')) {
			$this->geentoegang();
		}
		$root = $this->model->getMenuTree($menu_name, true);
		if (!$root OR ! $root->magBeheren()) {
			$this->geentoegang();
		}
		$body = new MenuBeheerView($root);
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('/layout/css/menubeheer');
	}

	public function toevoegen($parent_id) {
		if ($parent_id == 'favoriet') {
			$parent = $this->model->getMenuRoot(LoginModel::getUid());
		} else {
			$parent = $this->model->getMenuItem((int) $parent_id);
		}
		if (!$parent OR ! $parent->magBeheren()) {
			$this->geentoegang();
		}
		$item = $this->model->newMenuItem($parent->item_id);
		if (!$item OR ! $item->magBeheren()) {
			$this->geentoegang();
		}
		$this->view = new MenuItemForm($item, $this->action, $parent_id); // fetches POST values itself
		if ($this->view->validate()) {
			$item->item_id = (int) $this->model->create($item);
			setMelding('Toegevoegd: ' . $item->tekst, 1);
			$this->view = new JsonResponse(true);
		}
	}

	public function bewerken($item_id) {
		$item = $this->model->getMenuItem((int) $item_id);
		if (!$item OR ! $item->magBeheren()) {
			$this->geentoegang();
		}
		$parent = $this->model->getMenuItem($item->parent_id);
		if (!$parent OR ! $parent->magBeheren()) {
			$this->geentoegang();
		}
		$this->view = new MenuItemForm($item, $this->action, $item->item_id); // fetches POST values itself
		if ($this->view->validate()) {
			$rowcount = $this->model->update($item);
			if ($rowcount > 0) {
				setMelding($item->tekst . ' bijgewerkt', 1);
			} else {
				setMelding($item->tekst . ' ongewijzigd', -1);
			}
			$this->view = new JsonResponse(true);
		}
	}

	public function verwijderen($item_id) {
		$item = $this->model->getMenuItem((int) $item_id);
		if (!$item OR ! $item->magBeheren()) {
			$this->geentoegang();
		}
		$rowcount = $this->model->removeMenuItem($item);
		setMelding($item->tekst . ' verwijderd', 1);
		if ($rowcount > 0) {
			setMelding($rowcount . ' menu-items niveau omhoog verplaatst.', 2);
		}
		$this->view = new JsonResponse(true);
	}

	public function zichtbaar($item_id) {
		$item = $this->model->getMenuItem((int) $item_id);
		if (!$item OR ! $item->magBeheren()) {
			$this->geentoegang();
		}
		$item->zichtbaar = !$item->zichtbaar;
		$rowcount = $this->model->update($item);
		if ($rowcount > 0) {
			setMelding($item->tekst . ($item->zichtbaar ? ' ' : ' on') . 'zichtbaar gemaakt', 1);
		} else {
			setMelding($item->tekst . ' ongewijzigd', -1);
		}
		$this->view = new JsonResponse(true);
	}

}
