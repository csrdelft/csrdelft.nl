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

				$subscription = $webPushRepository->findOneBy([
					'clientEndpoint' => $endpoint,
				]);
				if ($subscription) {
					// Voor nu is er maar een subscription per account toegestaan
					return new JsonResponse(['success' => false]);
				}

				$subscription = $webPushRepository->nieuw();
				$subscription->clientEndpoint = $endpoint;
				$subscription->clientKeys = json_encode($keys);

				$webPushRepository->save($subscription);
				return new JsonResponse(['success' => true]);
			case 'PUT':
				$endpoint = $request->request->get('endpoint');
				$keys = $request->request->get('keys');

				$subscription = $webPushRepository->findOneBy([
					'client_endpoint' => $endpoint,
				]);
				$subscription->clientKeys = json_encode($keys);

				$webPushRepository->save($subscription);
				return new JsonResponse(['success' => true]);
			case 'DELETE':
				$endpoint = $request->request->get('endpoint');

				$subscription = $webPushRepository->findOneBy([
					'client_endpoint' => $endpoint,
				]);

				$webPushRepository->remove($subscription);
				return new JsonResponse(['success' => true]);
			default:
				return new JsonResponse(['success' => false]);
		}
	}
}
