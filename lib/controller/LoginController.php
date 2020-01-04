<?php

namespace CsrDelft\controller;

use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\model\security\RememberLoginModel;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\view\cms\CmsPaginaView;
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
	 * @var RememberLoginModel
	 */
	private $rememberLoginModel;
	/**
	 * @var CmsPaginaRepository
	 */
	private $cmsPaginaRepository;

	public function __construct(LoginModel $loginModel, RememberLoginModel $rememberLoginModel, CmsPaginaRepository $cmsPaginaRepository) {
		$this->rememberLoginModel = $rememberLoginModel;
		$this->loginModel = $loginModel;
		$this->cmsPaginaRepository = $cmsPaginaRepository;
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
			// Remember login form
			if ($values['remember']) {
				$remember = $this->rememberLoginModel->nieuw();
				$form = new RememberAfterLoginForm($remember, $values['redirect']);
				$form->css_classes[] = 'redirect';

				$body = new CmsPaginaView($this->cmsPaginaRepository->find(instelling('stek', 'homepage')));
				return view('default', ['content' => $body, 'modal' => $form]);
			}
			if ($values['redirect']) {
				return $this->redirect(urldecode($values['redirect']));
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
		setMelding('U bekijkt de webstek nu als ' . ProfielModel::getNaam($uid, 'volledig') . '!', 1);
		return $this->redirect(HTTP_REFERER);
	}

	public function endsu() {
		if (!$this->loginModel->isSued()) {
			setMelding('Niet gesued!', -1);
		} else {
			$this->loginModel->endSwitchUser();
			setMelding('Switch-useractie is beëindigd.', 1);
		}
		return $this->redirect(HTTP_REFERER);
	}
}
