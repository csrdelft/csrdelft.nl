<?php

namespace CsrDelft\controller\api\v3;

use Symfony\Component\Routing\Attribute\Route;
use CsrDelft\common\Annotation\Auth;
use CsrDelft\controller\AbstractController;
use CsrDelft\service\BarSysteemService;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class BarSysteemBeheerController
 * @package CsrDelft\controller\api\v3
 */
#[Route(path: '/api/v3/barbeheer')]
class BarSysteemBeheerController extends AbstractController
{
	public function __construct(
		private readonly BarSysteemService $barSysteemService
	) {
	}

	/**
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[IsGranted('ROLE_OAUTH2_BAR:BEHEER')]
	#[Route(path: '/grootboek', methods: ['GET'])]
	public function grootboek()
	{
		return $this->json($this->barSysteemService->getGrootboekInvoer());
	}

	/**
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[IsGranted('ROLE_OAUTH2_BAR:BEHEER')]
	#[Route(path: '/grootboeken', methods: ['GET'])]
	public function grootboeken()
	{
		return $this->json($this->barSysteemService->getGrootboeken());
	}

	/**
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[IsGranted('ROLE_OAUTH2_BAR:BEHEER')]
	#[Route(path: '/tools', methods: ['GET'])]
	public function tools()
	{
		return $this->json($this->barSysteemService->getToolData());
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[IsGranted('ROLE_OAUTH2_BAR:BEHEER')]
	#[Route(path: '/addProduct', methods: ['POST'])]
	public function addProduct(Request $request)
	{
		$name = $request->request->get('name');
		$price = $request->request->get('price');
		$type = $request->request->get('grootboekId');

		return $this->json(
			$this->barSysteemService->addProduct($name, $price, $type)
		);
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[IsGranted('ROLE_OAUTH2_BAR:BEHEER')]
	#[Route(path: '/updatePrice', methods: ['POST'])]
	public function updatePrice(Request $request)
	{
		$productId = $request->request->get('productId');
		$price = $request->request->get('price');

		return $this->json(
			$this->barSysteemService->updatePrice($productId, $price)
		);
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[IsGranted('ROLE_OAUTH2_BAR:BEHEER')]
	#[Route(path: '/updateVisibility', methods: ['POST'])]
	public function updateVisibility(Request $request)
	{
		$visibility = $request->request->get('visibility');
		$productId = $request->request->get('productId');

		return $this->json(
			$this->barSysteemService->updateVisibility($productId, $visibility)
		);
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[IsGranted('ROLE_OAUTH2_BAR:BEHEER')]
	#[Route(path: '/addPerson', methods: ['POST'])]
	public function addPerson(Request $request)
	{
		$name = $request->request->get('name');
		$saldo = $request->request->get('saldo');
		$uid = $request->request->get('uid');

		return $this->json(
			$this->barSysteemService->addPerson($name, $saldo, $uid)
		);
	}

	/**
	 * @param Request $request
	 * @return JsonResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[IsGranted('ROLE_OAUTH2_BAR:BEHEER')]
	#[Route(path: '/removePerson', methods: ['POST'])]
	public function removePerson(Request $request)
	{
		$id = $request->request->get('id');

		return $this->json($this->barSysteemService->removePerson($id));
	}
}
