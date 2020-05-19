<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\entity\GoogleToken;
use CsrDelft\repository\GoogleTokenRepository;
use CsrDelft\service\GoogleSync;
use CsrDelft\service\security\LoginService;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GoogleController.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class GoogleController extends AbstractController {
	/**
	 * @var GoogleTokenRepository
	 */
	private $googleTokenModel;

	public function __construct(GoogleTokenRepository $googleTokenModel) {
		$this->googleTokenModel = $googleTokenModel;
	}

	public function callback(Request $request) {
		$state = $request->query->get('state', null);
		$code = $request->query->get('code', null);
		$error = $request->query->get('error',null);
		if ($code) {
			$client = GoogleSync::createGoogleCLient();
			$client->fetchAccessTokenWithAuthCode($code);

			$googleToken = new GoogleToken();
			$googleToken->uid = LoginService::getUid();
			$googleToken->token = $client->getRefreshToken();

			$manager = $this->getDoctrine()->getManager();
			$manager->persist($googleToken);
			$manager->flush();

			return $this->csrRedirect(urldecode($state));
		}

		if ($error) {
			setMelding('Verbinding met Google niet geaccepteerd', 2);
			$state = substr(strstr($state, 'addToGoogleContacts', true), 0, -1);

			return $this->csrRedirect($state);
		}

		throw new CsrException('Geen error en geen code van Google gekregen.');
	}
}
