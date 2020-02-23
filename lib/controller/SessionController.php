<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\model\entity\security\RememberLogin;
use CsrDelft\model\security\LoginModel;
use CsrDelft\model\security\RememberLoginModel;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\login\LoginSessionsData;
use CsrDelft\view\login\RememberLoginData;
use CsrDelft\view\login\RememberLoginForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 28/07/2019
 */
class SessionController {
	/**
	 * @var LoginModel
	 */
	private $loginModel;
	/**
	 * @var RememberLoginModel
	 */
	private $rememberLoginModel;

	public function __construct(LoginModel $loginModel, RememberLoginModel $rememberLoginModel) {
		$this->loginModel = $loginModel;
		$this->rememberLoginModel = $rememberLoginModel;
	}

	public function sessionsdata() {
		return new LoginSessionsData($this->loginModel->find('uid = ?', array(LoginModel::getUid())));
	}

	public function endsession($session_hash = null) {
		$session = false;
		if ($session_hash) {
			$session = $this->loginModel->find('session_hash = ? AND uid = ?', array($session_hash, LoginModel::getUid()), null, null, 1)->fetch();
		}
		if (!$session) {
			throw new CsrToegangException();
		}
		$deleted = $this->loginModel->delete($session);
		return new RemoveRowsResponse(array($session), $deleted === 1 ? 200 : 404);
	}

	public function lockip() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (!$selection) {
			throw new CsrToegangException();
		}
		$response = array();
		foreach ($selection as $UUID) {
			/** @var RememberLogin $remember */
			$remember = $this->rememberLoginModel->retrieveByUUID($UUID);
			if (!$remember || $remember->uid !== LoginModel::getUid()) {
				throw new CsrToegangException();
			}
			$remember->lock_ip = !$remember->lock_ip;
			$this->rememberLoginModel->update($remember);
			$response[] = $remember;
		}
		return new RememberLoginData($response);
	}

	public function rememberdata() {
		return new RememberLoginData($this->rememberLoginModel->find('uid = ?', array(LoginModel::getUid())));
	}

	public function remember() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (isset($selection[0])) {
			$remember = $this->rememberLoginModel->retrieveByUUID($selection[0]);
		} else {
			$remember = $this->rememberLoginModel->nieuw();
		}
		if (!$remember || $remember->uid !== LoginModel::getUid()) {
			throw new CsrToegangException();
		}
		$form = new RememberLoginForm($remember);
		if ($form->validate()) {
			if ($remember->id) {
				$this->rememberLoginModel->update($remember);
			} else {
				$this->rememberLoginModel->rememberLogin($remember);
			}
			if (isset($_POST['DataTableId'])) {
				return new RememberLoginData(array($remember));
			} else if (!empty($_POST['redirect'])) {
				return new JsonResponse($_POST['redirect']);
			}
			else {
				return new JsonResponse(CSR_ROOT);
			}
		} else {
			return $form;
		}
	}

	public function forgetAll() {
		$remembers = $this->rememberLoginModel->find('uid = ?', [LoginModel::getUid()])->fetchAll();

		foreach ($remembers as $remember) {
			$this->rememberLoginModel->delete($remember);
		}

		return new RemoveRowsResponse($remembers);
	}

	public function forget() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (!$selection) {
			throw new CsrToegangException();
		}
		$response = array();
		foreach ($selection as $UUID) {
			/** @var RememberLogin $remember */
			$remember = $this->rememberLoginModel->retrieveByUUID($UUID);
			if (!$remember || $remember->uid !== LoginModel::getUid()) {
				throw new CsrToegangException();
			}
			$this->rememberLoginModel->delete($remember);
			$response[] = $remember;
		}
		return new RemoveRowsResponse($response);
	}
}
