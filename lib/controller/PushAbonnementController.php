<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\repository\instellingen\LidInstellingenRepository;
use CsrDelft\repository\PushAbonnementRepository;
use CsrDelft\service\security\LoginService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PushAbonnementController extends AbstractController
{
	/**
	 * @param PushAbonnementRepository $pushAbonnementRepository
	 * @param LidInstellingenRepository $lidInstellingenRepository
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/push-abonnement", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function nieuw(
		PushAbonnementRepository $pushAbonnementRepository,
		LidInstellingenRepository $lidInstellingenRepository,
		Request $request
	) {
		$endpoint = $request->request->get('endpoint');
		$keys = $request->request->get('keys');

		$subscription = $pushAbonnementRepository->findOneBy([
			'client_endpoint' => $endpoint,
		]);
		// De endpoint kan niet vaker voorkomen
		if ($subscription !== null) {
			return new JsonResponse(['success' => false], 400);
		}

		$subscription = $pushAbonnementRepository->nieuw();
		$subscription->client_endpoint = $endpoint;
		$subscription->client_keys = json_encode($keys);

		$pushAbonnementRepository->save($subscription);

		$lidInstellingenRepository->wijzigInstelling('forum', 'meldingPush', 'ja');

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
		// Kan niet aanpassen als de endpoint niet bestaat
		if ($subscription === null) {
			return new JsonResponse(['success' => false], 400);
		}

		$subscription->client_keys = json_encode($keys);

		$pushAbonnementRepository->save($subscription);
		return new JsonResponse(['success' => true]);
	}

	/**
	 * @param PushAbonnementRepository $pushAbonnementRepository
	 * @param LidInstellingenRepository $lidInstellingenRepository
	 * @param Request $request
	 * @return JsonResponse
	 * @Route("/push-abonnement", methods={"DELETE"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function verwijderen(
		PushAbonnementRepository $pushAbonnementRepository,
		LidInstellingenRepository $lidInstellingenRepository,
		Request $request
	) {
		$endpoint = $request->request->get('endpoint');

		$subscription = $pushAbonnementRepository->findOneBy([
			'client_endpoint' => $endpoint,
		]);
		// Kan niet verwijderen als de endpoint niet bestaat
		if ($subscription === null) {
			return new JsonResponse(['success' => false], 400);
		}

		$pushAbonnementRepository->remove($subscription);

		$subscriptionCount = $pushAbonnementRepository->count([
			'uid' => $subscription->uid,
		]);
		if ($subscriptionCount <= 0) {
			$lidInstellingenRepository->wijzigInstelling(
				'forum',
				'meldingPush',
				'nee'
			);
		}

		return new JsonResponse(['success' => true]);
	}
}
