<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\MenuModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\MeldingResponse;
use CsrDelft\view\menubeheer\MenuItemForm;

/**
 * MenuBeheerController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MenuBeheerController {
	/**
	 * @var MenuModel
	 */
	private $menuModel;

	public function __construct(MenuModel $menuModel) {
		$this->menuModel = $menuModel;
	}

	public function beheer($menu_name = 'main') {
		if ($menu_name != LoginModel::getUid() AND !LoginModel::mag(P_ADMIN)) {
			throw new CsrToegangException();
		}
		$root = $this->menuModel->getMenu($menu_name);
		if (!$root OR !$root->magBeheren()) {
			throw new CsrToegangException();
		}
		return view('menubeheer.tree', [
			'root' => $root,
			'menus' => $this->menuModel->getMenuBeheerLijst(),
		]);
	}

	public function toevoegen($parent_id) {
		if ($parent_id == 'favoriet') {
			$parent = $this->menuModel->getMenuRoot(LoginModel::getUid());
		} else {
			$parent = $this->menuModel->getMenuItem((int)$parent_id);
		}
		if (!$parent OR !$parent->magBeheren()) {
			throw new CsrToegangException();
		}
		$item = $this->menuModel->nieuw($parent->item_id);
		if (!$item OR !$item->magBeheren()) {
			throw new CsrToegangException();
		}
		$form = new MenuItemForm($item, 'toevoegen', $parent_id); // fetches POST values itself
		if ($form->validate()) { // form checks if hidden fields are modified
			$this->menuModel->create($item);
			setMelding('Toegevoegd: ' . $item->tekst, 1);
			return new MeldingResponse();
		} else {
			return $form;
		}
	}

	public function bewerken($item_id) {
		$item = $this->menuModel->getMenuItem((int)$item_id);
		if (!$item OR !$item->magBeheren()) {
			throw new CsrToegangException();
		}
		$form = new MenuItemForm($item, 'bewerken', $item->item_id); // fetches POST values itself
		if ($form->validate()) { // form checks if hidden fields are modified
			$rowCount = $this->menuModel->update($item);
			if ($rowCount > 0) {
				setMelding($item->tekst . ' bijgewerkt', 1);
			} else {
				setMelding($item->tekst . ' ongewijzigd', 0);
			}
			return new JsonResponse(true);
		} else {
			return $form;
		}
	}

	public function verwijderen($item_id) {
		$item = $this->menuModel->getMenuItem((int)$item_id);
		if (!$item OR !$item->magBeheren()) {
			throw new CsrToegangException();
		}
		$rowCount = $this->menuModel->removeMenuItem($item);
		setMelding($item->tekst . ' verwijderd', 1);
		if ($rowCount > 0) {
			setMelding($rowCount . ' menu-items niveau omhoog verplaatst.', 2);
		}
		return new JsonResponse(true);
	}

	public function zichtbaar($item_id) {
		$item = $this->menuModel->getMenuItem((int)$item_id);
		if (!$item OR !$item->magBeheren()) {
			throw new CsrToegangException();
		}
		$item->zichtbaar = !$item->zichtbaar;
		$rowCount = $this->menuModel->update($item);
		if ($rowCount > 0) {
			setMelding($item->tekst . ($item->zichtbaar ? ' ' : ' on') . 'zichtbaar gemaakt', 1);
		} else {
			setMelding($item->tekst . ' ongewijzigd', 0);
		}
		return new JsonResponse(true);
	}
}
