<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\entity\Mail;
use CsrDelft\model\entity\security\AuthenticationMethod;
use CsrDelft\model\entity\security\RememberLogin;
use CsrDelft\model\ProfielModel;
use CsrDelft\model\security\AccessModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\model\security\OneTimeTokensModel;
use CsrDelft\model\security\RememberLoginModel;
use CsrDelft\view\cms\CmsPaginaView;
use CsrDelft\view\CsrLayoutOweePage;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\datatable\RemoveRowsResponse;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\login\AccountForm;
use CsrDelft\view\login\LoginForm;
use CsrDelft\view\login\LoginSessionsData;
use CsrDelft\view\login\RememberAfterLoginForm;
use CsrDelft\view\login\RememberLoginData;
use CsrDelft\view\login\RememberLoginForm;
use CsrDelft\view\login\WachtwoordVergetenForm;
use CsrDelft\view\login\WachtwoordWijzigenForm;

/**
 * LoginController.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Controller van de agenda.
 */
class LoginController {
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
				return new CsrLayoutPage($body, [], $form);
			}
			if ($values['redirect']) {
				redirect($values['redirect']);
			}
			redirect(CSR_ROOT);
		} else {
			redirect(CSR_ROOT . "#login");
		}
	}

	public function logout() {
		$this->loginModel->logout();
		redirect(CSR_ROOT);
	}

	public function su($uid = null) {
		$this->loginModel->switchUser($uid);
		setMelding('U bekijkt de webstek nu als ' . ProfielModel::getNaam($uid, 'volledig') . '!', 1);
		redirect(HTTP_REFERER, false);
	}

	public function endsu() {
		if (!$this->loginModel->isSued()) {
			setMelding('Niet gesued!', -1);
		} else {
			$this->loginModel->endSwitchUser();
			setMelding('Switch-useractie is beëindigd.', 1);
		}
		redirect(HTTP_REFERER, false);
	}
}
