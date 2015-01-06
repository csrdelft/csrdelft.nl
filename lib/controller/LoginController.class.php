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
			'verify'	 => 'P_PUBLIC',
			'sessions'	 => 'P_LOGGED_IN',
			'endsession' => 'P_LOGGED_IN'
		);
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		parent::performAction($this->getParams(2));
	}

	public function login() {
		require_once 'view/LoginView.class.php';
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
		setMelding('U bekijkt de webstek nu als ' . Lid::naamLink($uid, 'volledig', 'plain') . '!', 1);
		redirect(HTTP_REFERER, false);
	}

	public function endsu() {
		if (!$this->model->isSued()) {
			setMelding('Niet gesued!', -1);
		} else {
			$this->model->endSwitchUser();
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

		require_once 'model/CmsPaginaModel.class.php';
		require_once 'view/CmsPaginaView.class.php';

		$body = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('mobiel'));
		$this->view = new CsrLayoutPage($body);
	}

	public function wachtwoord($action = null) {
		$lid = $this->model->getLid();
		$uid = $lid->getUid();
		// wijzigen
		if (LoginModel::mag('P_PROFIEL_EDIT')) {
			$form = new WachtwoordWijzigenForm($lid, $action);
			if ($form->validate()) {
				$pw = $form->findByName('wwreset')->getValue();
				// wachtwoord opslaan
				if (!$lid->resetWachtwoord($pw)) {
					setMelding('Wachtwoord instellen faalt', -1);
					redirect();
				}
				setMelding('Wachtwoord instellen geslaagd', 1);
			}
		}
		// resetten
		elseif ($action === 'reset' AND LoginModel::mag('P_PROFIEL_EDIT', true) AND VerifyModel::instance()->isVerified($uid, '/wachtwoord/reset')) {
			$form = new WachtwoordWijzigenForm($lid, $action, false);
			if ($form->validate()) {
				$pw = $form->findByName('wwreset')->getValue();
				// wachtwoord opslaan
				if (!$lid->resetWachtwoord($pw)) {
					setMelding('Wachtwoord instellen faalt', -1);
					redirect();
				}
				setMelding('Wachtwoord instellen geslaagd', 1);
				// token verbruikt
				VerifyModel::instance()->discardToken($uid, '/wachtwoord/reset');
				$this->model->login($uid, $pw);
				$lidnaam = $lid->getNaamLink('civitas', 'plain');
				// stuur bevestigingsmail
				require_once 'model/entity/Mail.class.php';
				$bericht = "Geachte " . $lidnaam . ",\n\nU heeft recent uw wachtwoord opnieuw ingesteld. Als u dit niet zelf gedaan heeft dan moet u nu direct uw wachtwoord wijzigen en de PubCie op de hoogte stellen.\n\nMet amicale groet,\nUw PubCie";
				$mail = new Mail(array($lid->getEmail() => $lidnaam), 'C.S.R. webstek: nieuw wachtwoord ingesteld', $bericht);
				$mail->send();
			}
		}
		// vergeten
		else {
			$form = new WachtwoordVergetenForm();
			if ($form->validate()) {
				$values = $form->getValues();
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
				elseif ($lid instanceof Lid AND AccessModel::mag($lid, 'P_PROFIEL_EDIT') AND $lid->getEmail() === $values['mail']) {
					$token = VerifyModel::instance()->createToken($uid, '/wachtwoord/reset');
					$lidnaam = $lid->getNaamLink('civitas', 'plain');
					// stuure resetmail
					require_once 'model/entity/Mail.class.php';
					$bericht = "Geachte " . $lidnaam .
							",\n\nU heeft verzocht om uw wachtwoord opnieuw in te stellen. Dit is mogelijk met de onderstaande link tot " . $token->expire .
							".\n\n[url=" . CSR_ROOT . "/verify/" . $token->token .
							"]Wachtwoord instellen[/url].\n\nAls dit niet uw eigen verzoek is kunt u dit bericht negeren.\n\nMet amicale groet,\nUw PubCie";
					$mail = new Mail(array($lid->getEmail() => $lidnaam), 'C.S.R. webstek: nieuw wachtwoord instellen', $bericht);
					$mail->send();
					setMelding('Wachtwoord reset email verzonden', 1);
					/**
					 * Sowieso timeout geven zodat je geen bruteforce kan doen als je uid en email weet.
					 * (wachtwoord proberen, bij timeout vergeten mail sturen en dan weer wachtworod proberen, etc.)
					 */
					TimeoutModel::instance()->fout($uid);
				} else {
					TimeoutModel::instance()->fout($uid);
				}
			}
		}
		$this->view = new CsrLayoutPage($form);
	}

	public function verify() {
		$tokenValue = filter_input(INPUT_GET, 'onetime_token', FILTER_SANITIZE_STRING);
		$form = new VerifyForm($tokenValue);
		if ($form->validate()) {
			$uid = $form->findByName('user')->getValue();
			$lid = LidCache::getLid($uid);
			if ($lid instanceof Lid AND AccessModel::mag($lid, 'P_LOGGED_IN') AND VerifyModel::instance()->verifyToken($lid->getUid(), $tokenValue)) {
				// redirect by verifyToken
			}
			setMelding(VerifyModel::instance()->getError(), -1);
		}
		$this->view = new CsrLayoutPage($form);
	}

	public function sessions() {
		$this->view = new SessionsData(LoginModel::getUid());
	}

	public function endsession($sessionid) {
		$session = $this->model->find('session_id = ? AND uid = ?', array($sessionid, LoginModel::getUid()), null, null, 1)->fetch();
		$deleted = 0;
		if ($session) {
			$deleted = $this->model->delete($session);
		}
		$this->view = new RemoveRowsResponse(array($session), $deleted === 1 ? 200 : 404);
	}

}
