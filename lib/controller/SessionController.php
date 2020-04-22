<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\entity\security\RememberLogin;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\security\RememberLoginRepository;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\login\LoginSessionsData;
use CsrDelft\view\login\RememberLoginForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 28/07/2019
 */
class SessionController extends AbstractController {
	/**
	 * @var LoginModel
	 */
	private $loginModel;
	/**
	 * @var RememberLoginRepository
	 */
	private $rememberLoginRepository;

	public function __construct(LoginModel $loginModel, RememberLoginRepository $rememberLoginRepository) {
		$this->loginModel = $loginModel;
		$this->rememberLoginRepository = $rememberLoginRepository;
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
		return new RemoveRowsResponse([$session], $deleted === 1 ? 200 : 404);
	}

	public function lockip() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (!$selection) {
			throw new CsrToegangException();
		}
		$response = [];
		$manager = $this->getDoctrine()->getManager();
		foreach ($selection as $UUID) {
			/** @var RememberLogin $remember */
			$remember = $this->rememberLoginRepository->retrieveByUUID($UUID);
			if (!$remember || $remember->uid !== LoginModel::getUid()) {
				throw new CsrToegangException();
			}
			$remember->lock_ip = !$remember->lock_ip;
			$manager->persist($remember);
			$response[] = $remember;
		}
		$manager->flush();
		return $this->tableData($response);
	}

	public function rememberdata() {
		return $this->tableData($this->rememberLoginRepository->findBy(['uid' => LoginModel::getUid()]));
	}

	public function remember() {
		$selection = $this->getDataTableSelection();
		if (isset($selection[0])) {
			$remember = $this->rememberLoginRepository->retrieveByUUID($selection[0]);
		} else {
			$remember = $this->rememberLoginRepository->nieuw();
		}
		if (!$remember || $remember->uid !== LoginModel::getUid()) {
			throw new CsrToegangException();
		}
		$form = new RememberLoginForm($remember);
		if ($form->validate()) {
			if ($remember->id) {
				$this->getDoctrine()->getManager()->persist($remember);
				$this->getDoctrine()->getManager()->flush();
			} else {
				$this->rememberLoginRepository->rememberLogin($remember);
			}
			if (isset($_POST['DataTableId'])) {
				return $this->tableData([$remember]);
			} else if (!empty($_POST['redirect'])) {
				return new JsonResponse($_POST['redirect']);
			} else {
				return new JsonResponse(CSR_ROOT);
			}
		} else {
			return $form;
		}
	}

	public function forgetAll() {
		$remembers = $this->rememberLoginRepository->findBy(['uid' => LoginModel::getUid()]);

		$response = [];
		$manager = $this->getDoctrine()->getManager();
		foreach ($remembers as $remember) {
			$response[] = new RemoveDataTableEntry($remember->id, RememberLogin::class);
			$manager->remove($remember);
		}
		$manager->flush();

		return $this->tableData($response);
	}

	public function forget() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (!$selection) {
			throw new CsrToegangException();
		}
		$response = [];
		$manager = $this->getDoctrine()->getManager();
		foreach ($selection as $UUID) {
			/** @var RememberLogin $remember */
			$remember = $this->rememberLoginRepository->retrieveByUUID($UUID);
			if (!$remember || $remember->uid !== LoginModel::getUid()) {
				throw new CsrToegangException();
			}
			$manager->remove($remember);
			$response[] = new RemoveDataTableEntry($remember->id, RememberLogin::class);
		}
		$manager->flush();
		return $this->tableData($response);
	}
}
