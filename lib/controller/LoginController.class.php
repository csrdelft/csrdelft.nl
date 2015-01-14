<?php

require_once 'model/security/OneTimeTokensModel.class.php';

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
			'account'	 => 'P_LOGGED_IN',
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
		setMelding('U bekijkt de webstek nu als ' . ProfielModel::getNaam($uid, 'volledig') . '!', 1);
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

	public function account($uid = null, $delete = null) {
		if ($uid === null OR ! LoginModel::mag('P_ADMIN')) {
			$uid = LoginModel::getUid();
		}
		// aanvragen
		if ($uid === 'x999') {
			require_once 'model/CmsPaginaModel.class.php';
			require_once 'view/CmsPaginaView.class.php';
			if (isPosted()) {
				parent::geentoegang();
			}
			$body = new CmsPaginaView(CmsPaginaModel::instance()->getPagina('accountaanvragen'));
			$this->view = new CsrLayoutPage($body);
			return;
		}
		$account = AccountModel::get($uid);
		if (!$account AND LoginModel::mag('P_ADMIN')) {
			$account = AccountModel::instance()->maakAccount($uid);
		}
		if ($delete === 'delete' AND LoginModel::mag('P_ADMIN')) {
			$result = AccountModel::instance()->delete($account);
			if ($result === 1) {
				setMelding('Account succesvol verwijderd', 1);
				redirect('/profiel/' . $uid);
			}
			setMelding('Account verwijderen mislukt', -1);
		}
		$form = new AccountForm($account);
		if ($form->validate()) {
			if ($form->findByName('username')->getValue() == '') {
				$account->username = $account->uid;
			}
			// username, email & wachtwoord opslaan
			$pass_plain = $form->findByName('wijzigww')->getValue();
			AccountModel::instance()->wijzigWachtwoord($account, $pass_plain);
			setMelding('Inloggegevens wijzigen geslaagd', 1);
		}
		$this->view = new CsrLayoutPage($form);
	}

	public function wachtwoord($action = null) {
		$account = LoginModel::getAccount();
		// wijzigen
		if ($action !== 'vergeten' AND LoginModel::mag('P_PROFIEL_EDIT')) {
			$form = new WachtwoordWijzigenForm($account, $action);
			if ($form->validate()) {
				// wachtwoord opslaan
				$pass_plain = $form->findByName('wijzigww')->getValue();
				AccountModel::instance()->wijzigWachtwoord($account, $pass_plain);
				setMelding('Wachtwoord instellen geslaagd', 1);
			}
		}
		// resetten
		elseif ($action === 'reset' AND LoginModel::mag('P_PROFIEL_EDIT', true) AND OneTimeTokensModel::instance()->isVerified($account->uid, '/wachtwoord/reset')) {
			$form = new WachtwoordWijzigenForm($account, $action, false);
			if ($form->validate()) {
				// wachtwoord opslaan
				$pass_plain = $form->findByName('wijzigww')->getValue();
				AccountModel::instance()->wijzigWachtwoord($account, $pass_plain);
				setMelding('Wachtwoord instellen geslaagd', 1);
				// token verbruikt
				OneTimeTokensModel::instance()->discardToken($account->uid, '/wachtwoord/reset');
				// inloggen zonder $authByToken
				$this->model->login($account->uid, $pass_plain);
				// stuur bevestigingsmail
				$lidnaam = $account->getProfiel()->getNaam('civitas');
				require_once 'model/entity/Mail.class.php';
				$bericht = "Geachte " . $lidnaam .
						",\n\nU heeft recent uw wachtwoord opnieuw ingesteld. Als u dit niet zelf gedaan heeft dan moet u nu direct uw wachtwoord wijzigen en de PubCie op de hoogte stellen.\n\nMet amicale groet,\nUw PubCie";
				$mail = new Mail(array($account->email => $lidnaam), '[C.S.R. webstek] Nieuw wachtwoord ingesteld', $bericht);
				$mail->send();
			}
		}
		// vergeten
		else {
			$form = new WachtwoordVergetenForm();
			if ($form->validate()) {
				$values = $form->getValues();
				$account = AccountModel::get($values['user']);
				if (!$account) {
					$account = AccountModel::get('x999');
				}
				$timeout = AccountModel::instance()->moetWachten($account);
				if ($timeout > 0) {
					setMelding('Wacht ' . $timeout . ' seconden', -1);
				}
				// mag wachtwoord resetten?
				elseif (AccessModel::mag($account, 'P_PROFIEL_EDIT') AND $account->email === $values['mail']) {
					$token = OneTimeTokensModel::instance()->createToken($account->uid, '/wachtwoord/reset');
					// stuur resetmail
					$lidnaam = $account->getProfiel()->getNaam('civitas');
					require_once 'model/entity/Mail.class.php';
					$bericht = "Geachte " . $lidnaam .
							",\n\nU heeft verzocht om uw wachtwoord opnieuw in te stellen. Dit is mogelijk met de onderstaande link tot " . $token->expire .
							".\n\n[url=" . CSR_ROOT . "/verify/" . $token->token .
							"]Wachtwoord instellen[/url].\n\nAls dit niet uw eigen verzoek is kunt u dit bericht negeren.\n\nMet amicale groet,\nUw PubCie";
					$mail = new Mail(array($account->email => $lidnaam), '[C.S.R. webstek] Wachtwoord vergeten', $bericht);
					$mail->send();
					setMelding('Wachtwoord reset email verzonden', 1);
				} else {
					AccountModel::instance()->failedLoginAttempt($account);
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
			$account = AccountModel::get($uid);
			if ($account AND AccessModel::mag($account, 'P_LOGGED_IN') AND OneTimeTokensModel::instance()->verifyToken($account->uid, $tokenValue)) {
				// redirect by verifyToken
			}
			setMelding('Je kunt deze link maar 1x gebruiken', -1);
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
