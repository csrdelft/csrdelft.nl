<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrToegangException;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\MeldingResponse;
use CsrDelft\view\menubeheer\MenuItemForm;
use CsrDelft\view\renderer\TemplateView;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MenuBeheerController {
	/**
	 * @var MenuItemRepository
	 */
	private $menuItemRepository;

	public function __construct(MenuItemRepository $menuItemRepository) {
		$this->menuItemRepository = $menuItemRepository;
	}

	/**
	 * @param string $menu_name
	 * @return TemplateView
	 * @Route("/menubeheer/beheer/{menu_name}", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function beheer($menu_name = 'main') {
		if ($menu_name != LoginService::getUid() && !LoginService::mag(P_ADMIN)) {
			throw new CsrToegangException();
		}
		$root = $this->menuItemRepository->getMenu($menu_name);
		if (!$root || !$root->magBeheren()) {
			throw new CsrToegangException();
		}
		return view('menubeheer.tree', [
			'root' => $root,
			'menus' => $this->menuItemRepository->getMenuBeheerLijst(),
		]);
	}

	/**
	 * @param $parent_id
	 * @return MeldingResponse|MenuItemForm
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/menubeheer/toevoegen/{parent_id}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function toevoegen($parent_id) {
		if ($parent_id == 'favoriet') {
			$parent = $this->menuItemRepository->getMenuRoot(LoginService::getUid());
		} else {
			$parent = $this->menuItemRepository->getMenuItem((int)$parent_id);
		}
		if (!$parent || !$parent->magBeheren()) {
			throw new CsrToegangException();
		}
		$item = $this->menuItemRepository->nieuw($parent);
		if (!$item || !$item->magBeheren()) {
			throw new CsrToegangException();
		}
		$form = new MenuItemForm($item, 'toevoegen', $parent_id); // fetches POST values itself
		if ($form->validate()) { // form checks if hidden fields are modified
			$this->menuItemRepository->persist($item);
			setMelding('Toegevoegd: ' . $item->tekst, 1);
			return new MeldingResponse();
		} else {
			return $form;
		}
	}

	/**
	 * @param $item_id
	 * @return JsonResponse|MenuItemForm
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/menubeheer/bewerken/{item_id}", methods={"POST"}, requirements={"item_id": "\d+"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function bewerken($item_id) {
		$item = $this->menuItemRepository->getMenuItem((int)$item_id);
		if (!$item || !$item->magBeheren()) {
			throw new CsrToegangException();
		}
		$form = new MenuItemForm($item, 'bewerken', $item->item_id); // fetches POST values itself
		if ($form->validate()) { // form checks if hidden fields are modified
			$rowCount = $this->menuItemRepository->persist($item);
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

	/**
	 * @param $item_id
	 * @return JsonResponse
	 * @Route("/menubeheer/verwijderen/{item_id}", methods={"POST"}, requirements={"item_id": "\d+"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function verwijderen($item_id) {
		$item = $this->menuItemRepository->getMenuItem((int)$item_id);
		if (!$item || !$item->magBeheren()) {
			throw new CsrToegangException();
		}
		$rowCount = $this->menuItemRepository->removeMenuItem($item);
		setMelding($item->tekst . ' verwijderd', 1);
		if ($rowCount > 0) {
			setMelding($rowCount . ' menu-items niveau omhoog verplaatst.', 2);
		}
		return new JsonResponse(true);
	}

	/**
	 * @param $item_id
	 * @return JsonResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/menubeheer/zichtbaar/{item_id}", methods={"POST"}, requirements={"item_id": "\d+"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function zichtbaar($item_id) {
		$item = $this->menuItemRepository->getMenuItem((int)$item_id);
		if (!$item || !$item->magBeheren()) {
			throw new CsrToegangException();
		}
		$item->zichtbaar = !$item->zichtbaar;
		$this->menuItemRepository->persist($item);
		setMelding($item->tekst . ($item->zichtbaar ? ' ' : ' on') . 'zichtbaar gemaakt', 1);
		return new JsonResponse(true);
	}
}
