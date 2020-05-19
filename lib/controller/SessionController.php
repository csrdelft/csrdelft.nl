<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrToegangException;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\entity\security\LoginSession;
use CsrDelft\entity\security\RememberLogin;
use CsrDelft\repository\security\LoginSessionRepository;
use CsrDelft\repository\security\RememberLoginRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\login\RememberLoginForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 28/07/2019
 */
class SessionController extends AbstractController {
	/**
	 * @var LoginSessionRepository
	 */
	private $loginSessionRepository;
	/**
	 * @var RememberLoginRepository
	 */
	private $rememberLoginRepository;

	public function __construct(LoginSessionRepository $loginSessionRepository, RememberLoginRepository $rememberLoginRepository) {
		$this->loginSessionRepository = $loginSessionRepository;
		$this->rememberLoginRepository = $rememberLoginRepository;
	}

	public function sessionsdata() {
		$loginSession = $this->loginSessionRepository->findBy(['uid' => LoginService::getUid()]);
		return $this->tableData($loginSession);
	}

	public function endsession($session_hash) {
		$session = $this->loginSessionRepository->find($session_hash);
		$removed = new RemoveDataTableEntry($session_hash, LoginSession::class);

		if (!$session || $session->uid !== LoginService::getUid()) {
			throw new CsrToegangException();
		}

		$this->loginSessionRepository->removeByHash($session_hash);

		return $this->tableData([$removed]);
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
			if (!$remember || $remember->uid !== LoginService::getUid()) {
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
		return $this->tableData($this->rememberLoginRepository->findBy(['uid' => LoginService::getUid()]));
	}

	public function remember() {
		$selection = $this->getDataTableSelection();
		if (isset($selection[0])) {
			$remember = $this->rememberLoginRepository->retrieveByUUID($selection[0]);
		} else {
			$remember = $this->rememberLoginRepository->nieuw();
		}
		if (!$remember || $remember->uid !== LoginService::getUid()) {
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
		$remembers = $this->rememberLoginRepository->findBy(['uid' => LoginService::getUid()]);

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
			if (!$remember || $remember->uid !== LoginService::getUid()) {
				throw new CsrToegangException();
			}
			$manager->remove($remember);
			$response[] = new RemoveDataTableEntry($remember->id, RememberLogin::class);
		}
		$manager->flush();
		return $this->tableData($response);
	}
}
