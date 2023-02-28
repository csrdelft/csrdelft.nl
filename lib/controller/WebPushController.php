<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\repository\WebPushRepository;
use CsrDelft\service\security\LoginService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WebPushController extends AbstractController
{
	/**
	 * @param WebPushRepository $webPushRepository
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/webpush-subscription", methods={"POST", "PUT", "DELETE"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function subscription(
		WebPushRepository $webPushRepository,
		Request $request
	) {
		switch ($request->getMethod()) {
			case 'POST':
				$endpoint = $request->request->get('endpoint');
				$keys = $request->request->get('keys');

				$item = $webPushRepository->nieuw();
				$item->clientEndpoint = $endpoint;
				$item->clientKeys = json_encode($keys);

				$webPushRepository->save($item);
				return new JsonResponse(['success' => true]);
			case 'PUT':
				$endpoint = $request->request->get('endpoint');
				$keys = $request->request->get('keys');

				$item = $webPushRepository->findOneBy([
					'uid' => LoginService::getUid(),
				]);
				$item->clientEndpoint = $endpoint;
				$item->clientKeys = json_encode($keys);

				$webPushRepository->save($item);
				return new JsonResponse(['success' => true]);
			case 'DELETE':
				$item = $webPushRepository->findOneBy([
					'uid' => LoginService::getUid(),
				]);

				$webPushRepository->remove($item);
				return new JsonResponse(['success' => true]);
			default:
				return new JsonResponse(['success' => false]);
		}
	}
}
