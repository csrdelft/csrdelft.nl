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
			'verify'	 => 'P_PUBLIC'
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
		if ($form->validate() AND $this->model->login($values['user'], $values['pass'])) {
			if ($values['mobiel']) {
				$this->pauper();
				return;
			}
			redirect($values['url'], false);
		}
		redirect(CSR_ROOT);
	}

	public function logout() {
		$wasPauper = $this->model->isPauper();
		$this->model->logout();
		if ($wasPauper) {
			$this->pauper();
			return;
		}
		redirect(CSR_ROOT);
	}

	public function su($uid = null) {
		$this->model->switchUser($uid);
		setMelding('U bekijkt de webstek nu als ' . Lid::naamLink($uid, 'full', 'plain') . '!', 1);
		redirect(HTTP_REFERER, false);
	}

	public function endsu() {
		if (!$this->model->isSued()) {
			setMelding('Niet gesued!', -1);
		} else {
			LoginModel::instance()->endSwitchUser();
			setMelding('Switch-useractie is beëindigd.', 1);
		}
		redirect(HTTP_REFERER, false);
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
		// resetten
		if ($action === 'reset') {

			// is in deze sessie geverifieerd?
			if (isset($_SESSION['_verifiedUid'])) {

				$lid = LidCache::getLid($_SESSION['_verifiedUid']);
				if ($lid instanceof Lid) {
					$uid = $lid->getUid();
				} else {
					$uid = 'x999';
				}

				// mag wachtwoord resetten?
				if ($lid instanceof Lid AND AccessModel::mag($lid, 'P_LOGGED_IN') AND VerifyModel::instance()->isVerified($uid, '/wachtwoord/reset')) {

					$this->view = new WachtwoordResetForm($lid);
					if ($this->view->validate()) {
						$pw = $this->view->findByName('wwreset')->getValue();

						// wachtwoord opslaan
						if ($lid->resetWachtwoord($pw)) {
							setMelding('Wachtwoord instellen geslaagd', 1);

							// token verbruikt
							VerifyModel::instance()->discardToken($uid, '/wachtwoord/reset');

							if ($this->model->login($uid, $pw)) {
								redirect(CSR_ROOT);
							}
						}
						setMelding('Wachtwoord instellen faalt', -1);
						redirect();
					} else {
						$this->view = new CsrLayoutPage($this->view);
						return;
					}
				}
			}
		}
		// wachtwoord vergeten
		$this->view = new WachtwoordVergetenForm();
		if ($this->view->validate()) {
			$values = $this->view->getValues();
			$lid = LidCache::getLid($values['user']);
			if ($lid instanceof Lid) {
				$uid = $lid->getUid();
			} else {
				$uid = 'x999';
			}
			$timeout = TimeoutModel::instance()->moetWachten($uid);
			if ($timeout > 0) {
				setMelding('Wacht ' . $timeout . ' seconden', -1);
			}
			// mag wachtwoord resetten?
			elseif ($lid instanceof Lid AND AccessModel::mag($lid, 'P_LOGGED_IN') AND $lid->getEmail() == $values['mail']) {
				TimeoutModel::instance()->goed($uid);
				$token = VerifyModel::instance()->createToken($uid, '/wachtwoord/reset');

				require_once 'MVC/model/entity/Mail.class.php';
				$bericht = 'Geachte ' . $lid->getNaamLink('civitas', 'plain') .
						",\n\nU heeft verzocht om uw wachtwoord opnieuw in te stellen. Dit is mogelijk met de onderstaande link tot " . $token->expire .
						".\n\n[url=http://csrdelft.nl/verify/" . $token->token .
						"]Wachtwoord instellen[/url].\n\nAls dit niet uw eigen verzoek is kunt u dit bericht negeren.\n\nMet amicale groet,\nUw PubCie";
				$mail = new Mail(array($uid . '@csrdelft.nl' => Lid::naamLink($uid, 'civitas', 'plain')), 'C.S.R. webstek: nieuw wachtwoord instellen', $bericht);
				$mail->setReplyTo('no-reply@csrdelft.nl');
				$mail->send();

				setMelding('Wachtwoord reset email verzonden', 1);
				redirect();
			} else {
				TimeoutModel::instance()->fout($uid);
			}
		}
		$this->view = new CsrLayoutPage($this->view);
	}

	function verify() {
		$tokenValue = urldecode(filter_input(INPUT_GET, 'onetime_token', FILTER_SANITIZE_STRING));
		$this->view = new VerifyForm($tokenValue);
		if ($this->view->validate()) {
			$uid = $this->view->findByName('user')->getValue();
			$lid = LidCache::getLid($uid);
			if ($lid instanceof Lid AND AccessModel::mag($lid, 'P_LOGGED_IN') AND VerifyModel::instance()->verifyToken($lid->getUid(), $tokenValue)) {
				// redirect by verifyToken
			}
			setMelding(VerifyModel::instance()->getError(), -1);
		}
		$this->view = new CsrLayoutPage($this->view);
	}

}
