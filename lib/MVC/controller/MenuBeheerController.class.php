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
		// fetch menu naam
		if ($this->action === 'beheer' AND $this->hasParam(3)) {
			$naam = $this->getParam(3);
		} else {
			$naam = filter_input(INPUT_POST, 'menu');
		}
		// check beheer rechten
		if (empty($naam) OR $naam === 'main') {
			// P_ADMIN voor main
			if (!LoginModel::mag('P_ADMIN')) {
				$this->geentoegang();
			}
		} else {
			// lidnummer voor persoonlijk menu
			if (!LoginModel::mag($naam)) {
				$this->geentoegang();
			}
		}
		parent::performAction($this->getParams(3));
	}

	public function beheer($naam = '') {
		$root = $this->model->getMenuTree($naam, LoginModel::mag('P_ADMIN'));
		$body = new MenuBeheerView($root, $this->model->getBeheerMenusVoorLid());
		$this->view = new CsrLayoutPage($body);
		$this->view->addStylesheet('/layout/css/menubeheer');
	}

	public function toevoegen($parent_id) {
		$item = $this->model->newMenuItem((int) $parent_id);
		$this->view = new MenuItemForm($item, $this->action, (int) $parent_id); // fetches POST values itself
		if ($this->view->validate()) {
			$item->item_id = (int) $this->model->create($item);
			SimpleHTML::setMelding('Toegevoegd', 1);
			$this->view = new JsonResponse(true);
		}
	}

	public function bewerken($item_id) {
		$item = $this->model->getMenuItem($item_id);
		$this->view = new MenuItemForm($item, $this->action, $item->item_id); // fetches POST values itself
		if ($this->view->validate()) {
			$rowcount = $this->model->update($item);
			if ($rowcount > 0) {
				SimpleHTML::setMelding($item->tekst . ' bijgewerkt', 1);
			} else {
				SimpleHTML::setMelding($item->tekst . ' ongewijzigd', -1);
			}
			$this->view = new JsonResponse(true);
		}
	}

	public function verwijderen($item_id) {
		$item = $this->model->getMenuItem($item_id);
		$rowcount = $this->model->removeMenuItem($item);
		SimpleHTML::setMelding($item->tekst . ' verwijderd', 1);
		if ($rowcount > 0) {
			SimpleHTML::setMelding($rowcount . ' menu-items niveau omhoog verplaatst.', 2);
		}
		$this->view = new JsonResponse(true);
	}

	public function zichtbaar($item_id) {
		$item = $this->model->getMenuItem($item_id);
		$item->zichtbaar = !$item->zichtbaar;
		$rowcount = $this->model->update($item);
		if ($rowcount > 0) {
			SimpleHTML::setMelding($item->tekst . ($item->zichtbaar ? ' ' : ' on') . 'zichtbaar gemaakt', 1);
		} else {
			SimpleHTML::setMelding($item->tekst . ' ongewijzigd', -1);
		}
		$this->view = new JsonResponse(true);
	}

}
