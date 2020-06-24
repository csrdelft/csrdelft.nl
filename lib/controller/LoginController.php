<?php

namespace CsrDelft\controller;

use CsrDelft\common\Annotation\Auth;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\security\RememberLoginRepository;
use CsrDelft\service\security\LoginService;
use CsrDelft\service\security\SuService;
use CsrDelft\view\login\LoginForm;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * LoginController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de agenda.
 */
class LoginController extends AbstractController {
	/**
	 * @var LoginService
	 */
	private $loginService;
	/**
	 * @var RememberLoginRepository
	 */
	private $rememberLoginRepository;
	/**
	 * @var SuService
	 */
	private $suService;

	public function __construct(LoginService $loginService, SuService $suService, RememberLoginRepository $rememberLoginRepository) {
		$this->rememberLoginRepository = $rememberLoginRepository;
		$this->loginService = $loginService;
		$this->suService = $suService;
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @Route("/login", methods={"GET"})
	 * @Auth(P_PUBLIC)
	 */
	public function loginForm (Request $request) {
		$response = new Response(view('layout-extern.login', ['loginForm' => new LoginForm()]));

		// Als er geredirect wordt, stuur dan een forbidden status
		if ($request->query->has('redirect')) {
			$response->setStatusCode(Response::HTTP_FORBIDDEN);
		}

		return $response;
	}

	/**
	 * @return RedirectResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @throws Exception
	 * @Route("/login", methods={"POST"})
	 * @Auth(P_PUBLIC)
	 */
	public function login() {
		$form = new LoginForm(); // fetches POST values itself
		$values = $form->getValues();

		if ($form->validate() && $this->loginService->login($values['user'], $values['pass'])) {
			if ($values['remember']) {
				$remember = $this->rememberLoginRepository->nieuw();
				$this->rememberLoginRepository->rememberLogin($remember);
			}

			if ($values['redirect']) {
				return $this->csrRedirect(urldecode($values['redirect']));
			}
			return $this->redirectToRoute('default');
		} else {
			if ($values['redirect']) {
				return $this->redirectToRoute('csrdelft_login_loginform', ['redirect' => $values['redirect']]);
			}

			return $this->redirectToRoute('csrdelft_login_loginform');
		}
	}

	/**
	 * @return RedirectResponse
	 * @throws ORMException
	 * @throws OptimisticLockException
	 * @Route("/logout", methods={"GET","POST"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function logout() {
		$this->loginService->logout();
		return $this->redirectToRoute('default');
	}

	/**
	 * @param null $uid
	 * @return RedirectResponse
	 * @Route("/su/{uid}", methods={"GET"}, requirements={"uid": ".{4}"})
	 * @Auth(P_ADMIN)
	 */
	public function su($uid = null) {
		$this->suService->switchUser($uid);
		setMelding('U bekijkt de webstek nu als ' . ProfielRepository::getNaam($uid, 'volledig') . '!', 1);
		return $this->csrRedirect(HTTP_REFERER);
	}

	/**
	 * @return RedirectResponse
	 * @Route("/endsu", methods={"GET"})
	 * @Auth(P_LOGGED_IN)
	 */
	public function endsu() {
		if (!$this->suService->isSued()) {
			setMelding('Niet gesued!', -1);
		} else {
			$this->suService->endSwitchUser();
			setMelding('Switch-useractie is beëindigd.', 1);
		}
		return $this->csrRedirect(HTTP_REFERER);
	}
}
