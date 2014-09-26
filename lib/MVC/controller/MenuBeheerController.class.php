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
				'beheer' => 'P_ADMIN'
			);
		} else {
			$this->acl = array(
				'toevoegen'		 => 'P_ADMIN',
				'bewerken'		 => 'P_ADMIN',
				'verwijderen'	 => 'P_ADMIN'
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

	public function beheer($menu_naam = '') {
		$menu_naam = str_replace('%20', ' ', $menu_naam); // eigenlijk rawurldecode()
		$body = new MenuBeheerView($this->model->getMenuTree($menu_naam, true), $this->model->getAlleMenus());
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
				SimpleHTML::setMelding('Bijgewerkt', 1);
			} else {
				SimpleHTML::setMelding('Geen wijzigingen', 0);
			}
			$this->view = new JsonResponse(true);
		}
	}

	public function verwijderen($item_id) {
		$item = $this->model->getMenuItem($item_id);
		$this->model->removeMenuItem($item);
		SimpleHTML::setMelding('Verwijderd', 1);
		$this->view = new JsonResponse(true);
	}

}
