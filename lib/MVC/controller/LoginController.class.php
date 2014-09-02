<?php

/**
 * LoginController.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Controller van de agenda.
 */
class LoginController extends AclController {

	public function __construct($query) {
		parent::__construct($query, LoginModel::instance());
		$this->acl = array(
			'login'	 => 'P_PUBLIC',
			'logout' => 'P_LOGGED_IN',
			'su'	 => 'P_ADMIN',
			'endsu'	 => 'P_LOGGED_IN',
			'pauper' => 'P_PUBLIC'
		);
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		parent::performAction($this->getParams(2));
	}

	public function login() {
		require_once 'MVC/view/LoginView.class.php';
		$form = new LoginForm(); // fetches POST values itself
		$values = $form->getValues();
		$this->model->setPauper($values['mobiel']);
		if ($form->validate()) {
			if ($this->model->login($values['user'], $values['pass'], false)) {
				redirect($values['url']);
			}
		}
		redirect(CSR_ROOT); // login gefaald
	}

	public function logout() {
		$this->model->logout();
		redirect(CSR_ROOT);
	}

	public function su($uid = null) {
		$this->model->switchUser($uid);
		SimpleHTML::setMelding('U bekijkt de webstek nu als ' . Lid::naamLink($uid, 'full', 'plain') . '!', 1);
		redirect(HTTP_REFERER);
	}

	public function endsu() {
		if (!$this->model->isSued()) {
			SimpleHTML::setMelding('Niet gesued!', -1);
		} else {
			LoginModel::instance()->endSwitchUser();
			SimpleHTML::setMelding('Switch-useractie is beëindigd.', 1);
		}
		redirect(HTTP_REFERER);
	}

	public function pauper($terug = null) {
		if ($terug === 'terug') {
			$this->model->setPauper(false);
			redirect(CSR_ROOT);
		} else {
			$this->model->setPauper(true);
		}

		require_once 'MVC/model/CmsPaginaModel.class.php';
		require_once 'MVC/view/CmsPaginaView.class.php';

		$body = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('mobiel'));
		$this->view = new CsrLayoutPage($body);
	}

}
