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
	private $model;

	public function __construct() {
		$this->model = GoogleTokenModel::instance();
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

			if ($this->model->exists($googleToken)) {
				$this->model->update($googleToken);
			} else {
				$this->model->create($googleToken);
			}

			return $this->redirect(urldecode($state));
		}

		if ($error) {
			setMelding('Verbinding met Google niet geaccepteerd', 2);
			$state = substr(strstr($state, 'addToGoogleContacts', true), 0, -1);

			return $this->redirect($state);
		}

		throw new CsrException('Geen error en geen code van Google gekregen.');
	}
}
