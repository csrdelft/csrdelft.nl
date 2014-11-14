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
			'login'		 => 'P_PUBLIC',
			'logout'	 => 'P_LOGGED_IN',
			'su'		 => 'P_ADMIN',
			'endsu'		 => 'P_LOGGED_IN',
			'pauper'	 => 'P_PUBLIC',
			'wachtwoord' => 'P_PUBLIC',
			'verify'	 => 'P_LOGGED_IN'
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
		if ($form->validate()) {
			$this->model->setPauper($values['mobiel']);
			if ($this->model->login($values['user'], $values['pass'])) {
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
		setMelding('U bekijkt de webstek nu als ' . Lid::naamLink($uid, 'full', 'plain') . '!', 1);
		if (startsWith(REQUEST_URI, '/su')) {
			redirect(CSR_ROOT);
		} else {
			redirect(HTTP_REFERER);
		}
	}

	public function endsu() {
		if (!$this->model->isSued()) {
			setMelding('Niet gesued!', -1);
		} else {
			LoginModel::instance()->endSwitchUser();
			setMelding('Switch-useractie is beëindigd.', 1);
		}
		if (startsWith(REQUEST_URI, '/endsu')) {
			redirect(CSR_ROOT);
		} else {
			redirect(HTTP_REFERER);
		}
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

	public function wachtwoord($action = null) {
		if ($action === 'instellen') { // resetten
			if (isset($_SESSION['_verifiedUid'])) {
				$lid = LidCache::getLid($_SESSION['_verifiedUid']);
				if ($lid instanceof Lid AND VerifyModel::instance()->isVerified($lid->getUid(), '/wachtwoord/instellen')) {
					$this->view = new WachtwoordInstellenForm($lid);
					if ($this->view->validate()) {
						$pw = $this->view->findByName('wwreset')->getValue();
						if ($lid->setProperty('password', $pw)) {
							if ($lid->save()) {
								setMelding('Wachtwoord instellen geslaagd', 1);
								VerifyModel::instance()->discardToken($lid->getUid(), '/wachtwoord/instellen');
								if ($this->model->login($lid->getUid(), $pw)) {
									redirect(CSR_ROOT);
								}
							}
						}
					}
				}
			}
		} else { // wachtwoord vergeten
			$this->view = new WachtwoordVergetenForm();
			if ($this->view->validate()) {
				$values = $this->view->getValues();
				$lid = LidCache::getLid($values['user']);
				if ($lid instanceof Lid AND $lid->getEmail() == $values['mail']) {
					$uid = $lid->getUid();
					$tokenValue = VerifyModel::instance()->createToken();
					require_once 'MVC/model/entity/Mail.class.php';
					$bericht = "Wachtwoord instellen: [url]http://csrdelft.nl/verify?token=" . $tokenValue . "[/url]\r\n";
					$mail = new Mail(array($uid . '@csrdelft.nl' => Lid::naamLink($uid, 'civitas', 'plain')), 'C.S.R. webstek: nieuw wachtwoord instellen', $bericht);
					$mail->setReplyTo('no-reply@csrdelft.nl');
					$mail->send();
					setMelding('Wachtwoord instellen email verzonden', 1);
				} else {
					if ($lid instanceof Lid) {
						$uid = $lid->getUid();
					} else {
						$uid = 'x999';
					}
					TimeoutModel::instance()->fout($uid);
				}
			}
		}
	}

	function verify() {
		$token = urldecode(filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING));
		if (VerifyModel::instance()->verifyToken(LoginModel::getUid(), $token)) {
			// redirect in validate
		} else {
			setMelding(VerifyModel::instance()->getError(), -1);
			redirect(CSR_ROOT);
		}
	}

}
