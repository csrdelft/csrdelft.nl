<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrException;
use CsrDelft\common\GoogleSync;
use CsrDelft\controller\framework\QueryParamTrait;
use CsrDelft\model\entity\GoogleToken;
use CsrDelft\model\GoogleTokenModel;
use CsrDelft\model\security\LoginModel;

/**
 * Class GoogleController.
 *
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 */
class GoogleController {
	use QueryParamTrait;

	private $model;

	public function __construct() {
		$this->model = GoogleTokenModel::instance();
	}

	public function callback() {
		$state = $this->hasParam('state') ? $this->getParam('state') : null;
		$code = $this->hasParam('code') ? $this->getParam('code') : null;
		$error = $this->hasParam('error') ? $this->getParam('error') : null;
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

			redirect(urldecode($state));
		}

		if ($error) {
			setMelding('Verbinding met Google niet geaccepteerd', 2);
			$state = substr(strstr($state, 'addToGoogleContacts', true), 0, -1);

			redirect($state);
		}

		throw new CsrException('Geen error en geen code van Google gekregen.');
	}
}
