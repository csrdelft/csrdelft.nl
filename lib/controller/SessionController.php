<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\common\datatable\RemoveDataTableEntry;
use CsrDelft\entity\security\LoginSession;
use CsrDelft\entity\security\RememberLogin;
use CsrDelft\repository\security\LoginSessionRepository;
use CsrDelft\repository\security\RememberLoginRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\datatable\GenericDataTableResponse;
use CsrDelft\view\login\RememberLoginForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\RememberMe\PersistentTokenBasedRememberMeServices;

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

	/**
	 * @return GenericDataTableResponse
	 * @Route("/session/sessionsdata", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function sessionsdata() {
		$loginSession = $this->loginSessionRepository->findBy(['uid' => LoginService::getUid()]);
		return $this->tableData($loginSession);
	}

	/**
	 * @param LoginSession $session
	 * @return GenericDataTableResponse
	 * @Route("/session/endsession/{session_hash}", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function endsession(LoginSession $session) {
		if ($session->uid !== LoginService::getUid()) {
			throw $this->createAccessDeniedException();
		}

		$removed = new RemoveDataTableEntry($session->session_hash, LoginSession::class);

		$this->getDoctrine()->getManager()->remove($session);
		$this->getDoctrine()->getManager()->flush();

		return $this->tableData([$removed]);
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/session/lockip", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function lockip() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (!$selection) {
			throw $this->createAccessDeniedException();
		}
		$response = [];
		$manager = $this->getDoctrine()->getManager();
		foreach ($selection as $UUID) {
			/** @var RememberLogin $remember */
			$remember = $this->rememberLoginRepository->retrieveByUUID($UUID);
			if (!$remember || $remember->uid !== LoginService::getUid()) {
				throw $this->createAccessDeniedException();
			}
			$remember->lock_ip = !$remember->lock_ip;
			$manager->persist($remember);
			$response[] = $remember;
		}
		$manager->flush();
		return $this->tableData($response);
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/session/rememberdata", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function rememberdata() {
		return $this->tableData($this->rememberLoginRepository->findBy(['uid' => LoginService::getUid()]));
	}

	/**
	 * @param Request $request
	 * @param PersistentTokenBasedRememberMeServices $rememberMeServices
	 * @return RememberLoginForm|Response
	 * @Route("/session/remember", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function remember(Request $request, PersistentTokenBasedRememberMeServices $rememberMeServices) {
		$selection = $this->getDataTableSelection();

		if (empty($selection)) {
			$response = new Response();

			$request->request->set('_remember_me', true);
			$rememberMeServices->loginSuccess($request, $response, $this->get('security.token_storage')->getToken());

			return $response;
		}

		$remember = $this->rememberLoginRepository->retrieveByUUID($selection[0]);

		if (!$remember || $remember->uid !== LoginService::getUid()) {
			throw $this->createAccessDeniedException();
		}
		$form = new RememberLoginForm($remember);
		if ($form->validate()) {
			if (isset($_POST['DataTableId'])) {
				$response = $this->tableData([$remember]);
			} else if (!empty($_POST['redirect'])) {
				$response = new JsonResponse($_POST['redirect']);
			} else {
				$response = new JsonResponse(CSR_ROOT);
			}

			$this->getDoctrine()->getManager()->persist($remember);
			$this->getDoctrine()->getManager()->flush();

			return $response;
		} else {
			return $form;
		}
	}

	/**
	 * @return GenericDataTableResponse
	 * @Route("/session/forget-all", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
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

	/**
	 * @return GenericDataTableResponse
	 * @Route("/session/forget", methods={"POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function forget() {
		$selection = $this->getDataTableSelection();
		if (!$selection) {
			throw $this->createAccessDeniedException();
		}
		$response = [];
		$manager = $this->getDoctrine()->getManager();
		foreach ($selection as $UUID) {
			/** @var RememberLogin $remember */
			$remember = $this->rememberLoginRepository->retrieveByUUID($UUID);
			if (!$remember || $remember->uid !== $this->getUser()->getUsername()) {
				throw $this->createAccessDeniedException();
			}
			$response[] = new RemoveDataTableEntry($remember->id, RememberLogin::class);
			$manager->remove($remember);
		}
		$manager->flush();
		return $this->tableData($response);
	}
}
