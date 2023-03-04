<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\repository\PushAbonnementRepository;
use CsrDelft\service\security\LoginService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PushAbonnementController extends AbstractController
{
	/**
	 * @param PushAbonnementRepository $pushAbonnementRepository
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/webpush-subscription", methods={"POST", "PUT", "DELETE"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function subscription(
		PushAbonnementRepository $pushAbonnementRepository,
		Request $request
	) {
		switch ($request->getMethod()) {
			case 'POST':
				$endpoint = $request->request->get('endpoint');
				$keys = $request->request->get('keys');

				$subscription = $pushAbonnementRepository->findOneBy([
					'clientEndpoint' => $endpoint,
				]);
				if ($subscription) {
					// Voor nu is er maar een subscription per account toegestaan
					return new JsonResponse(['success' => false]);
				}

				$subscription = $pushAbonnementRepository->nieuw();
				$subscription->clientEndpoint = $endpoint;
				$subscription->clientKeys = json_encode($keys);

				$pushAbonnementRepository->save($subscription);
				return new JsonResponse(['success' => true]);
			case 'PUT':
				$endpoint = $request->request->get('endpoint');
				$keys = $request->request->get('keys');

				$subscription = $pushAbonnementRepository->findOneBy([
					'client_endpoint' => $endpoint,
				]);
				$subscription->clientKeys = json_encode($keys);

				$pushAbonnementRepository->save($subscription);
				return new JsonResponse(['success' => true]);
			case 'DELETE':
				$endpoint = $request->request->get('endpoint');

				$subscription = $pushAbonnementRepository->findOneBy([
					'client_endpoint' => $endpoint,
				]);

				$pushAbonnementRepository->remove($subscription);
				return new JsonResponse(['success' => true]);
			default:
				return new JsonResponse(['success' => false]);
		}
	}
}
