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
	 * @Route("/push-abonnement", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function nieuw(
		PushAbonnementRepository $pushAbonnementRepository,
		Request $request
	) {
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
		$subscription->client_endpoint = $endpoint;
		$subscription->client_keys = json_encode($keys);

		$pushAbonnementRepository->save($subscription);
		return new JsonResponse(['success' => $subscription]);
	}

	/**
	 * @param PushAbonnementRepository $pushAbonnementRepository
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/push-abonnement", methods={"PUT"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function aanpassen(
		PushAbonnementRepository $pushAbonnementRepository,
		Request $request
	) {
		$endpoint = $request->request->get('endpoint');
		$keys = $request->request->get('keys');

		$subscription = $pushAbonnementRepository->findOneBy([
			'client_endpoint' => $endpoint,
		]);
		$subscription->client_keys = json_encode($keys);

		$pushAbonnementRepository->save($subscription);
		return new JsonResponse(['success' => true]);
	}

	/**
	 * @param PushAbonnementRepository $pushAbonnementRepository
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/push-abonnement", methods={"DELETE"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function verwijderen(
		PushAbonnementRepository $pushAbonnementRepository,
		Request $request
	) {
		$endpoint = $request->request->get('endpoint');

		$subscription = $pushAbonnementRepository->findOneBy([
			'client_endpoint' => $endpoint,
		]);

		$pushAbonnementRepository->remove($subscription);
		return new JsonResponse(['success' => true]);
	}
}
