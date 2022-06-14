<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\Security\Voter\Entity\MenuItemVoter;
use CsrDelft\repository\MenuItemRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\GenericSuggestiesResponse;
use CsrDelft\view\MeldingResponse;
use CsrDelft\view\menubeheer\MenuItemForm;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 */
class MenuBeheerController extends AbstractController
{
	/**
	 * @var MenuItemRepository
	 */
	private $menuItemRepository;

	public function __construct(MenuItemRepository $menuItemRepository)
	{
		$this->menuItemRepository = $menuItemRepository;
	}

	/**
	 * @param string $menuName
	 * @return Response
	 * @Route("/menubeheer/beheer/{menuName}", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function beheer($menuName = 'main'): Response
	{
		if ($menuName != $this->getUid() && !$this->mag(P_ADMIN)) {
			throw $this->createAccessDeniedException();
		}
		$root = $this->menuItemRepository->getMenuBeheer($menuName);
		$this->denyAccessUnlessGranted(MenuItemVoter::BEHEREN, $root);
		return $this->render('menubeheer/tree.html.twig', [
			'root' => $root,
			'menus' => $this->menuItemRepository->getMenuBeheerLijst(),
		]);
	}

	/**
	 * @param $parentId
	 * @return MeldingResponse|MenuItemForm
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/menubeheer/toevoegen/{parentId}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function toevoegen($parentId)
	{
		if ($parentId == 'favoriet') {
			$parent = $this->menuItemRepository->getMenuRoot($this->getUid());
		} else {
			$parent = $this->menuItemRepository->getMenuItem((int) $parentId);
		}
		$this->denyAccessUnlessGranted(MenuItemVoter::BEHEREN, $parent);
		$item = $this->menuItemRepository->nieuw($parent);
		$this->denyAccessUnlessGranted(MenuItemVoter::BEHEREN, $item);
		$form = new MenuItemForm($item, 'toevoegen', $parentId); // fetches POST values itself
		if ($form->validate()) {
			// form checks if hidden fields are modified
			$this->menuItemRepository->persist($item);
			setMelding('Toegevoegd: ' . $item->tekst, 1);
			return new MeldingResponse();
		} else {
			return $form;
		}
	}

	/**
	 * @param $itemId
	 * @return JsonResponse|MenuItemForm
	 * @Route("/menubeheer/bewerken/{itemId}", methods={"POST"}, requirements={"itemId": "\d+"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function bewerken($itemId)
	{
		$item = $this->menuItemRepository->getMenuItem((int) $itemId);
		$this->denyAccessUnlessGranted(MenuItemVoter::BEHEREN, $item);
		$form = new MenuItemForm($item, 'bewerken', $item->item_id); // fetches POST values itself
		if ($form->validate()) {
			// form checks if hidden fields are modified
			try {
				$this->menuItemRepository->persist($item);
				setMelding($item->tekst . ' bijgewerkt', 1);
			} catch (Exception $e) {
				setMelding($item->tekst . ' ongewijzigd', 0);
			}
			return new JsonResponse(true);
		} else {
			return $form;
		}
	}

	/**
	 * @param $itemId
	 * @return JsonResponse
	 * @Route("/menubeheer/verwijderen/{itemId}", methods={"POST"}, requirements={"itemId": "\d+"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function verwijderen($itemId): JsonResponse
	{
		$item = $this->menuItemRepository->getMenuItem((int) $itemId);
		$this->denyAccessUnlessGranted(MenuItemVoter::BEHEREN, $item);
		$rowCount = $this->menuItemRepository->removeMenuItem($item);
		setMelding($item->tekst . ' verwijderd', 1);
		if ($rowCount > 0) {
			setMelding($rowCount . ' menu-items niveau omhoog verplaatst.', 2);
		}
		return new JsonResponse(true);
	}

	/**
	 * @param $itemId
	 * @return JsonResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/menubeheer/zichtbaar/{itemId}", methods={"POST"}, requirements={"itemId": "\d+"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function zichtbaar($itemId): JsonResponse
	{
		$item = $this->menuItemRepository->getMenuItem((int) $itemId);
		$this->denyAccessUnlessGranted(MenuItemVoter::BEHEREN, $item);
		$item->zichtbaar = !$item->zichtbaar;
		$this->menuItemRepository->persist($item);
		setMelding(
			$item->tekst . ($item->zichtbaar ? ' ' : ' on') . 'zichtbaar gemaakt',
			1
		);
		return new JsonResponse(true);
	}

	/**
	 * @Route("/menubeheer/suggesties")
	 * @Auth(P_LOGGED_IN)
	 * @param Request $request
	 * @return GenericSuggestiesResponse
	 */
	public function suggesties(Request $request): GenericSuggestiesResponse
	{
		return new GenericSuggestiesResponse(
			$this->menuItemRepository->getSuggesties($request->query->get('q'))
		);
	}
}
