<?php

namespace CsrDelft\controller;

use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\model\security\RememberLoginModel;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\login\LoginForm;
use CsrDelft\view\login\RememberAfterLoginForm;

/**
 * LoginController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de agenda.
 */
class LoginController extends AbstractController {
	private $loginModel;
	private $rememberLoginModel;

	public function __construct() {
		$this->rememberLoginModel = RememberLoginModel::instance();
		$this->loginModel = LoginModel::instance();
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

				$body = new CmsPaginaView(CmsPaginaModel::get(instelling('stek', 'homepage')));
				return view('default', ['content' => $body, 'modal' => $form]);
			}
			if ($values['redirect']) {
				return $this->redirect($values['redirect']);
			}
			return $this->redirectToRoute('default');
		} else {
			return $this->redirectToRoute('default', ['_fragment', 'login']);
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
