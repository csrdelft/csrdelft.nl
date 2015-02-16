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
		if (!$this->isPosted()) {
			$this->acl = array(
				'logout'			 => 'P_LOGGED_IN',
				'su'				 => 'P_ADMIN',
				'endsu'				 => 'P_LOGGED_IN',
				'pauper'			 => 'P_PUBLIC',
				'account'			 => 'P_LOGGED_IN',
				'accountaanvragen'	 => 'P_PUBLIC',
				'wachtwoord'		 => 'P_PUBLIC',
				'verify'			 => 'P_PUBLIC'
			);
		} else {
			$this->acl = array(
				'login'				 => 'P_PUBLIC',
				'logout'			 => 'P_LOGGED_IN',
				'pauper'			 => 'P_PUBLIC',
				'account'			 => 'P_LOGGED_IN',
				'wachtwoord'		 => 'P_PUBLIC',
				'verify'			 => 'P_PUBLIC',
				'loginsessionsdata'	 => 'P_LOGGED_IN',
				'loginendsession'	 => 'P_LOGGED_IN',
				'loginlockip'		 => 'P_LOGGED_IN',
				'loginrememberdata'	 => 'P_LOGGED_IN',
				'loginremember'		 => 'P_LOGGED_IN',
				'loginforget'		 => 'P_LOGGED_IN'
			);
		}
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		parent::performAction($this->getParams(2));
	}

	protected function geentoegang() {
		require_once 'model/CmsPaginaModel.class.php';
		require_once 'view/CmsPaginaView.class.php';
		if ($this->isPosted()) {
			parent::geentoegang();
		}
		$body = new CmsPaginaView(CmsPaginaModel::get('accountaanvragen'));
		$this->view = new CsrLayoutPage($body);
	}

	public function login() {
		require_once 'view/LoginView.class.php';
		$form = new LoginForm(); // fetches POST values itself
		$values = $form->getValues();

		if ($form->validate() AND $this->model->login($values['user'], $values['pass'])) {

			// Switch to mobile webstek
			if ($values['pauper']) {
				$this->pauper();
			}
			// Remember login form
			if ($values['remember']) {
				$remember = RememberLoginModel::instance()->nieuw();
				$form = new RememberAfterLoginForm($remember);
				$form->css_classes[] = 'redirect';

				require_once 'model/CmsPaginaModel.class.php';
				require_once 'view/CmsPaginaView.class.php';

				$body = new CmsPaginaView(CmsPaginaModel::get(Instellingen::get('stek', 'homepage')));
				$this->view = new CsrLayoutPage($body, array(), $form);
				return;
			}
			if (isset($_COOKIE['goback'])) {
				redirect($_COOKIE['goback']);
			}
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
		$body = new CmsPaginaView(CmsPaginaModel::get('mobiel'));
		$this->view = new CsrLayoutPage($body);
	}

	public function accountaanvragen() {
		return $this->geentoegang();
	}

	public function account($uid = null, $delete = null) {
		if ($uid === null OR ! LoginModel::mag('P_ADMIN')) {
			$uid = LoginModel::getUid();
		}
		// aanvragen
		if ($uid === 'x999') {
			return $this->aanvragen();
		}
		// bewerken
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
				if (AccountModel::instance()->wijzigWachtwoord($account, $pass_plain)) {
					setMelding('Wachtwoord instellen geslaagd', 1);
				}
				// token verbruikt
				OneTimeTokensModel::instance()->discardToken($account->uid, '/wachtwoord/reset');
				// inloggen zonder $authByToken
				$this->model->login($account->uid, $pass_plain, false);
				// stuur bevestigingsmail
				$lidnaam = $account->getProfiel()->getNaam('civitas');
				require_once 'model/entity/Mail.class.php';
				$bericht = "Geachte " . $lidnaam .
						",\n\nU heeft recent uw wachtwoord opnieuw ingesteld. Als u dit niet zelf gedaan heeft dan moet u nu direct uw wachtwoord wijzigen en de PubCie op de hoogte stellen.\n\nMet amicale groet,\nUw PubCie";
				$mail = new Mail(array($account->email => $lidnaam), '[C.S.R. webstek] Nieuw wachtwoord ingesteld', $bericht);
				$mail->send();
				redirect(CSR_ROOT);
			}
		}
		// vergeten
		else {
			$form = new WachtwoordVergetenForm();
			if ($form->validate()) {
				// voorkom dat AccessModel ingelogde gebruiker blokkeerd met $allowPrivateUrl == false
				if (LoginModel::instance()->isAuthenticatedByToken()) {
					LoginModel::instance()->login('x999', 'x999', false);
				}
				$values = $form->getValues();
				$account = AccountModel::get($values['user']);
				// mag wachtwoord wijzigen?
				if ($account AND AccessModel::mag($account, 'P_PROFIEL_EDIT') AND mb_strtolower($account->email) === mb_strtolower($values['mail'])) {
					$token = OneTimeTokensModel::instance()->createToken($account->uid, '/wachtwoord/reset');
					// stuur resetmail
					$lidnaam = $account->getProfiel()->getNaam('civitas');
					require_once 'model/entity/Mail.class.php';
					$bericht = "Geachte " . $lidnaam .
							",\n\nU heeft verzocht om uw wachtwoord opnieuw in te stellen. Dit is mogelijk met de onderstaande link tot " . $token[1] .
							".\n\n[url=" . CSR_ROOT . "/verify/" . $token[0] .
							"]Wachtwoord instellen[/url].\n\nAls dit niet uw eigen verzoek is kunt u dit bericht negeren.\n\nMet amicale groet,\nUw PubCie";
					$mail = new Mail(array($account->email => $lidnaam), '[C.S.R. webstek] Wachtwoord vergeten', $bericht);
					$mail->send();
					setMelding('Wachtwoord reset email verzonden', 1);
				} else {
					setMelding('Lidnummer en/of e-mailadres onjuist', -1);
				}
			}
		}
		$this->view = new CsrLayoutPage($form);
	}

	public function verify($tokenString = null) {
		$tokenString = filter_var($tokenString, FILTER_SANITIZE_STRING);
		$form = new VerifyForm($tokenString);
		if ($form->validate()) {
			// voorkom dat AccessModel ingelogde gebruiker blokkeerd met $allowPrivateUrl == false
			if (LoginModel::instance()->isAuthenticatedByToken()) {
				LoginModel::instance()->login('x999', 'x999', false);
			}
			$uid = $form->findByName('user')->getValue();
			$account = AccountModel::get($uid);
			// mag inloggen?
			if ($account AND AccessModel::mag($account, 'P_LOGGED_IN') AND OneTimeTokensModel::instance()->verifyToken($account->uid, $tokenString)) {
				// redirect by verifyToken
			} else {
				setMelding('Deze link is niet meer geldig', -1);
			}
		}
		$this->view = new CsrLayoutPage($form);
	}

	public function loginsessionsdata() {
		$this->view = new LoginSessionsData($this->model->find('uid = ?', array(LoginModel::getUid())));
	}

	public function loginendsession($session_hash = null) {
		$session = false;
		if ($session_hash) {
			$session = $this->model->find('session_hash = ? AND uid = ?', array($session_hash, LoginModel::getUid()), null, null, 1)->fetch();
		}
		if (!$session) {
			$this->geentoegang();
		}
		$deleted = $this->model->delete($session);
		$this->view = new RemoveRowsResponse(array($session), $deleted === 1 ? 200 : 404);
	}

	public function loginlockip() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$response = array();
		foreach ($selection as $UUID) {
			$remember = RememberLoginModel::getUUID($UUID);
			if (!$remember OR $remember->uid !== LoginModel::getUid()) {
				$this->geentoegang();
			}
			$remember->lock_ip = !$remember->lock_ip;
			RememberLoginModel::instance()->update($remember);
			$response[] = $remember;
		}
		$this->view = new RememberLoginData($response);
	}

	public function loginrememberdata() {
		$this->view = new RememberLoginData(RememberLoginModel::instance()->find('uid = ?', array(LoginModel::getUid())));
	}

	public function loginremember() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (isset($selection[0])) {
			$remember = RememberLoginModel::getUUID($selection[0]);
		} else {
			$remember = RememberLoginModel::instance()->nieuw();
		}
		if (!$remember OR $remember->uid !== LoginModel::getUid()) {
			$this->geentoegang();
		}
		$form = new RememberLoginForm($remember);
		if ($form->validate()) {
			if ($remember->id) {
				RememberLoginModel::instance()->update($remember);
			} else {
				RememberLoginModel::instance()->rememberLogin($remember);
			}
			if (isset($_POST['DataTableId'])) {
				$this->view = new RememberLoginData(array($remember));
			}
			// after login
			elseif (isset($_COOKIE['goback'])) {
				$this->view = new JsonResponse($_COOKIE['goback']);
				unset($_COOKIE['goback']);
			} else {
				$this->view = new JsonResponse(CSR_ROOT);
			}
		} else {
			$this->view = $form;
		}
	}

	public function loginforget() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		$response = array();
		foreach ($selection as $UUID) {
			$remember = RememberLoginModel::getUUID($UUID);
			if (!$remember OR $remember->uid !== LoginModel::getUid()) {
				$this->geentoegang();
			}
			RememberLoginModel::instance()->delete($remember);
			$response[] = $remember;
		}
		$this->view = new RemoveRowsResponse($response);
	}

}
