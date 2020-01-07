<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\GoogleSync;
use CsrDelft\model\entity\GoogleToken;
use CsrDelft\model\GoogleTokenModel;
use CsrDelft\model\security\LoginModel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GoogleController.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class GoogleController extends AbstractController {
	/**
	 * @var GoogleTokenModel
	 */
	private $googleTokenModel;

	public function __construct(GoogleTokenModel $googleTokenModel) {
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
			$googleToken->uid = LoginModel::getUid();
			$googleToken->token = $client->getRefreshToken();

			if ($this->googleTokenModel->exists($googleToken)) {
				$this->googleTokenModel->update($googleToken);
			} else {
				$this->googleTokenModel->create($googleToken);
			}

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
