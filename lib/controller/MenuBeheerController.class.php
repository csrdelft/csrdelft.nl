<?php

require_once 'model/MenuModel.class.php';
require_once 'view/MenuBeheerView.class.php';

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

	public function beheer($menu_name = 'main') {
		if ($menu_name != LoginModel::getUid() AND ! LoginModel::mag('P_ADMIN')) {
			$this->geentoegang();
		}
		$root = $this->model->getMenu($menu_name);
		if (!$root OR ! $root->magBeheren()) {
			$this->geentoegang();
		}
		$body = new MenuBeheerView($root);
		$this->view = new CsrLayoutPage($body);
		$this->view->addCompressedResources('menubeheer');
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
		$item = $this->model->nieuw($parent->item_id);
		if (!$item OR ! $item->magBeheren()) {
			$this->geentoegang();
		}
		$form = new MenuItemForm($item, $this->action, $parent_id); // fetches POST values itself
		if ($form->validate()) { // form checks if hidden fields are modified
			$this->model->create($item);
			setMelding('Toegevoegd: ' . $item->tekst, 1);
			$this->view = new JsonResponse(true);
		} else {
			$this->view = $form;
		}
	}

	public function bewerken($item_id) {
		$item = $this->model->getMenuItem((int) $item_id);
		if (!$item OR ! $item->magBeheren()) {
			$this->geentoegang();
		}
		$form = new MenuItemForm($item, $this->action, $item->id); // fetches POST values itself
		if ($form->validate()) { // form checks if hidden fields are modified
			$rowCount = $this->model->update($item);
			if ($rowCount > 0) {
				setMelding($item->tekst . ' bijgewerkt', 1);
			} else {
				setMelding($item->tekst . ' ongewijzigd', 0);
			}
			$this->view = new JsonResponse(true);
		} else {
			$this->view = $form;
		}
	}

	public function verwijderen($item_id) {
		$item = $this->model->getMenuItem((int) $item_id);
		if (!$item OR ! $item->magBeheren()) {
			$this->geentoegang();
		}
		$rowCount = $this->model->removeMenuItem($item);
		setMelding($item->tekst . ' verwijderd', 1);
		if ($rowCount > 0) {
			setMelding($rowCount . ' menu-items niveau omhoog verplaatst.', 2);
		}
		$this->view = new JsonResponse(true);
	}

	public function zichtbaar($item_id) {
		$item = $this->model->getMenuItem((int) $item_id);
		if (!$item OR ! $item->magBeheren()) {
			$this->geentoegang();
		}
		$item->zichtbaar = !$item->zichtbaar;
		$rowCount = $this->model->update($item);
		if ($rowCount > 0) {
			setMelding($item->tekst . ($item->zichtbaar ? ' ' : ' on') . 'zichtbaar gemaakt', 1);
		} else {
			setMelding($item->tekst . ' ongewijzigd', 0);
		}
		$this->view = new JsonResponse(true);
	}

}
