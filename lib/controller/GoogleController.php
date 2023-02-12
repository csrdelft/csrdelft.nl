<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\CsrException;
use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\FlashType;
use CsrDelft\entity\GoogleToken;
use CsrDelft\repository\GoogleTokenRepository;
use CsrDelft\service\GoogleAuthenticator;
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
	/**
	 * @var GoogleTokenRepository
	 */
	private $googleTokenModel;

	public function __construct(GoogleTokenRepository $googleTokenModel)
	{
		$this->googleTokenModel = $googleTokenModel;
	}

	/**
	 * @param Request $request
	 * @param EntityManagerInterface $manager
	 * @return RedirectResponse
	 * @Route("/google/callback", methods={"GET", "POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function callback(
		Request $request,
		EntityManagerInterface $manager,
		GoogleAuthenticator $googleAuthenticator
	): RedirectResponse {
		$state = urldecode($request->query->get('state', null));

		if (!str_starts_with($state, $request->getSchemeAndHttpHost())) {
			throw new CsrGebruikerException('Redirect is niet binnen de stek!');
		}

		$code = $request->query->get('code', null);
		$error = $request->query->get('error', null);
		if ($code) {
			$client = $googleAuthenticator->createClient();
			$client->fetchAccessTokenWithAuthCode($code);

			$existingToken = $this->googleTokenModel->findOneBy([
				'uid' => $this->getUid(),
			]);

			if (!$existingToken) {
				$googleToken = new GoogleToken();
				$googleToken->uid = $this->getUid();
				$googleToken->token = $client->getRefreshToken();
				$manager->persist($googleToken);
			} else {
				$existingToken->token = $client->getRefreshToken();
			}

			$manager->flush();

			return $this->redirect($state);
		}

		if ($error) {
			$this->addFlash(
				FlashType::WARNING,
				'Verbinding met Google niet geaccepteerd'
			);
			$state = substr(strstr($state, 'addToGoogleContacts', true), 0, -1);

			return $this->redirect($state);
		}

		throw new CsrException('Geen error en geen code van Google gekregen.');
	}
}
