<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\FlashType;
use CsrDelft\entity\GoogleToken;
use CsrDelft\repository\GoogleTokenRepository;
use CsrDelft\service\GoogleClientManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GoogleController.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class GoogleController extends AbstractController
{
	public function __construct(
		private readonly GoogleTokenRepository $googleTokenModel
	) {
	}

	/**
	 * @param Request $request
	 * @param EntityManagerInterface $manager
	 * @return RedirectResponse
	 * @Auth(P_LOGGED_IN)
	 */
	#[Route(path: '/google/callback', methods: ['GET', 'POST'])]
	public function callback(
		Request $request,
		GoogleClientManager $googleClientManager
	): RedirectResponse {
		$code = $request->query->get('code', null);
		$error = $request->query->get('error', null);

		$state = urldecode($request->query->get('state', null));
		$session = $request->getSession();

		$state_cmp = null;

		if ($session->has('google_auth_state')) {
			$state_cmp = $session->get('google_auth_state');
			$session->remove('google_auth_state');
		}
		if ($state_cmp === null || !hash_equals($state_cmp, $state)) {
			throw new CsrGebruikerException(
				'Authenticatiestatus komt niet overeen met Google (' .
					$state_cmp .
					',' .
					$state .
					'). Probeer opnieuw'
			);
		}
		if (!str_contains($state, ':')) {
			throw new CsrException('Foute authentication state!!', 500);
		}
		$redirect = substr($state, strpos($state, ':') + 1);

		if (!str_starts_with($redirect, $request->getSchemeAndHttpHost())) {
			throw new CsrGebruikerException(
				'Redirect is niet binnen de stek! ' .
					$redirect .
					', ' .
					$request->getSchemeAndHttpHost()
			);
		}

		if ($code) {
			$client = $googleClientManager->getClient();
			$token = $client->fetchAccessTokenWithAuthCode($code);
			$request->getSession()->set('google_access_token', $token);

			return $this->redirect($redirect);
		}

		if ($error) {
			$this->addFlash(
				FlashType::WARNING,
				'Verbinding met Google niet geaccepteerd'
			);
			$state = substr(strstr($redirect, 'addToGoogleContacts', true), 0, -1);

			return $this->redirect($redirect);
		}

		throw new CsrException('Geen error en geen code van Google gekregen.');
	}
}
