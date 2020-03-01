<?php

namespace CsrDelft\controller;

use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\repository\ProfielRepository;
use CsrDelft\repository\security\RememberLoginRepository;
use CsrDelft\view\login\LoginForm;
use CsrDelft\view\login\RememberAfterLoginForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * LoginController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de agenda.
 */
class LoginController extends AbstractController {
	/**
	 * @var LoginModel
	 */
	private $loginModel;
	/**
	 * @var RememberLoginRepository
	 */
	private $rememberLoginRepository;

	public function __construct(LoginModel $loginModel, RememberLoginRepository $rememberLoginRepository) {
		$this->rememberLoginRepository = $rememberLoginRepository;
		$this->loginModel = $loginModel;
	}

	public function loginForm (Request $request) {
		$response = new Response(view('layout-extern.login', ['loginForm' => new LoginForm()]));

		// Als er geredirect wordt, stuur dan een forbidden status
		if ($request->query->has('redirect')) {
			$response->setStatusCode(Response::HTTP_FORBIDDEN);
		}

		return $response;
	}

	public function login() {
		$form = new LoginForm(); // fetches POST values itself
		$values = $form->getValues();

		if ($form->validate() && $this->loginModel->login($values['user'], $values['pass'])) {
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
				return $this->redirectToRoute('login-form', ['redirect' => $values['redirect']]);
			}

			return $this->redirectToRoute('login-form');
		}
	}

	public function logout() {
		$this->loginModel->logout();
		return $this->redirectToRoute('default');
	}

	public function su($uid = null) {
		$this->loginModel->switchUser($uid);
		setMelding('U bekijkt de webstek nu als ' . ProfielRepository::getNaam($uid, 'volledig') . '!', 1);
		return $this->csrRedirect(HTTP_REFERER);
	}

	public function endsu() {
		if (!$this->loginModel->isSued()) {
			setMelding('Niet gesued!', -1);
		} else {
			$this->loginModel->endSwitchUser();
			setMelding('Switch-useractie is beëindigd.', 1);
		}
		return $this->csrRedirect(HTTP_REFERER);
	}
}
