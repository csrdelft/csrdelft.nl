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
			'endSu'	 => 'P_LOGGED_IN',
			'pauper' => 'P_PUBLIC'
		);
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(2)) {
			$this->action = $this->getParam(2);
		}
		parent::performAction($this->getParams(3));
	}

	public function login() {
		$url = CSR_ROOT;
		$form = new LoginForm(); // fetches POST values itself
		if ($form->validate()) {
			$values = $form->getValues();
			$url = $values['url'];
		}
		invokeRefresh($url);
	}

	public function su($uid) {
		$this->model->switchUser($uid);
		setMelding('U bekijkt de webstek nu als ' . Lid::naamLink($uid, 'full', 'plain') . '!', 1);
	}

	public function endSu() {
		if (!$this->model->isSued()) {
			setMelding('Niet gesued!', -1);
		} else {
			LoginModel::instance()->endSwitchUser();
			setMelding('Switch-useractie is beëindigd.', 1);
		}
	}

	public function pauper($terug) {
		if ($terug === 'terug') {
			$this->model->setPauper(false);
			invokeRefresh(CSR_ROOT);
		} else {
			$this->model->setPauper(true);
		}

		require_once 'MVC/model/CmsPaginaModel.class.php';
		require_once 'MVC/view/CmsPaginaView.class.php';

		$body = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('mobiel'));
		$this->view = new CsrLayoutPage($body);
	}

}
