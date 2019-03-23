<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\controller\framework\AclController;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\entity\Mail;
use CsrDelft\model\entity\security\AuthenticationMethod;
use CsrDelft\model\entity\security\RememberLogin;
use CsrDelft\model\InstellingenModel;
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
 *
 * @property LoginModel $model
 */
class LoginController extends AclController {

	public function __construct($query) {
		parent::__construct($query, LoginModel::instance());
		if ($this->getMethod() == 'GET') {
			$this->acl = array(
				'logout' => P_LOGGED_IN,
				'su' => P_ADMIN,
				'endsu' => P_LOGGED_IN,
				'account' => P_PUBLIC,
				'accountaanvragen' => P_PUBLIC,
				'accountaanmaken' => P_ADMIN,
				'accountbewerken' => P_LOGGED_IN,
				'accountverwijderen' => P_LOGGED_IN,
				'wachtwoord' => P_PUBLIC,
				'wachtwoordwijzigen' => P_LOGGED_IN,
				'wachtwoordreset' => P_LOGGED_IN,
				'wachtwoordvergeten' => P_PUBLIC,
				'wachtwoordverlopen' => P_LOGGED_IN,
			);
		} else {
			$this->acl = array(
				'login' => P_PUBLIC,
				'logout' => P_LOGGED_IN,
				'account' => P_LOGGED_IN,
				'accountaanmaken' => P_ADMIN,
				'accountbewerken' => P_LOGGED_IN,
				'accountverwijderen' => P_LOGGED_IN,
				'wachtwoord' => P_PUBLIC,
				'wachtwoordwijzigen' => P_LOGGED_IN,
				'wachtwoordreset' => P_PUBLIC,
				'wachtwoordvergeten' => P_PUBLIC,
				'loginsessionsdata' => P_LOGGED_IN,
				'loginendsession' => P_LOGGED_IN,
				'loginlockip' => P_LOGGED_IN,
				'loginrememberdata' => P_LOGGED_IN,
				'loginremember' => P_LOGGED_IN,
				'loginforget' => P_LOGGED_IN
			);
		}
	}

	public function performAction(array $args = array()) {
		if ($this->hasParam(1)) {
			$this->action = $this->getParam(1);
		}
		parent::performAction($this->getParams(2));
	}

	protected function exit_http($response_code) {
		if ($this->getMethod() == 'POST') {
			parent::exit_http($response_code);
		}
		$this->GET_accountaanvragen();
		$this->view->view();
		exit;
	}

	public function login() {
		$form = new LoginForm(); // fetches POST values itself
		$values = $form->getValues();

		if ($form->validate() AND $this->model->login($values['user'], $values['pass'])) {
			// Remember login form
			if ($values['remember']) {
				$remember = RememberLoginModel::instance()->nieuw();
				$form = new RememberAfterLoginForm($remember, $values['redirect']);
				$form->css_classes[] = 'redirect';


				$body = new CmsPaginaView(CmsPaginaModel::get(InstellingenModel::get('stek', 'homepage')));
				$this->view = new CsrLayoutPage($body, array(), $form);
				return;
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
		$this->model->logout();
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

	public function account($uid = null, $action = null) {
		switch ($action) {
			case 'aanvragen':
				return $this->GET_accountaanvragen();
			case 'aanmaken':
				return $this->accountaanmaken($uid);
			case 'verwijderen':
				return $this->accountverwijderen($uid);
			case 'bewerken':
			default:
				return $this->accountbewerken($uid);
		}
	}

	public function GET_accountaanvragen() {
		$body = new CmsPaginaView(CmsPaginaModel::get('accountaanvragen'));
		if (!LoginModel::mag(P_LOGGED_IN)) {
			$this->view = new CsrLayoutOweePage($body);
		} else {
			$this->view = new CsrLayoutPage($body);
		}
	}

	public function accountaanmaken($uid = null) {
		if (!LoginModel::mag(P_ADMIN)) {
			$this->exit_http(403);
		}
		if ($uid == null) {
			$uid = $this->model->getUid();
		}
		if (AccountModel::get($uid)) {
			setMelding('Account bestaat al', 0);
		} else {
			$account = AccountModel::instance()->maakAccount($uid);
			if ($account) {
				setMelding('Account succesvol aangemaakt', 1);
			} else {
				throw new CsrGebruikerException('Account aanmaken gefaald');
			}
		}
		redirect('/account/' . $uid . '/bewerken');
	}

	public function accountbewerken($uid = null) {
		if ($uid == null) {
			$uid = $this->model->getUid();
		}
		if ($uid === 'x999') {
			$this->GET_accountaanvragen();
			return;
		}
		if ($uid !== $this->model->getUid() AND !LoginModel::mag(P_ADMIN)) {
			$this->exit_http(403);
		}
		if (LoginModel::instance()->getAuthenticationMethod() !== AuthenticationMethod::recent_password_login) {
			setMelding('U mag geen account wijzigen want u bent niet recent met wachtwoord ingelogd', 2);
			$this->exit_http(403);
		}
		$account = AccountModel::get($uid);
		if (!$account) {
			setMelding('Account bestaat niet', -1);
			$this->exit_http(403);
		}
		if (!AccessModel::mag($account, P_LOGGED_IN)) {
			setMelding('Account mag niet inloggen', 2);
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

	public function accountverwijderen($uid = null) {
		if ($uid == null) {
			$uid = $this->model->getUid();
		}
		if ($uid !== $this->model->getUid() AND !LoginModel::mag(P_ADMIN)) {
			$this->exit_http(403);
		}
		$account = AccountModel::get($uid);
		if (!$account) {
			setMelding('Account bestaat niet', -1);
		} else {
			$result = AccountModel::instance()->delete($account);
			if ($result === 1) {
				setMelding('Account succesvol verwijderd', 1);
			} else {
				setMelding('Account verwijderen mislukt', -1);
			}
		}
		$this->view = new JsonResponse('/profiel/' . $uid); // redirect
	}

	public function wachtwoord($action = null) {
		switch ($action) {
			case 'wijzigen':
				return $this->wachtwoordwijzigen();
			case 'reset':
				return $this->wachtwoordreset();
			case 'verlopen':
				return $this->wachtwoordwijzigen();
			case 'vergeten':
			default:
				return $this->wachtwoordvergeten();
		}
	}

	public function wachtwoordwijzigen() {
		$account = LoginModel::getAccount();
		// mag inloggen?
		if (!$account OR !AccessModel::mag($account, P_LOGGED_IN)) {
			$this->exit_http(403);
		}
		$form = new WachtwoordWijzigenForm($account, 'wijzigen');
		if ($form->validate()) {
			// wachtwoord opslaan
			$pass_plain = $form->findByName('wijzigww')->getValue();
			AccountModel::instance()->wijzigWachtwoord($account, $pass_plain);
			setMelding('Wachtwoord instellen geslaagd', 1);
		}
		$this->view = new CsrLayoutPage($form);
	}

	public function wachtwoordreset() {
		$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
		$account = OneTimeTokensModel::instance()->verifyToken('/wachtwoord/reset', $token);

		if ($account == null) {
			$this->exit_http(403);
		}
		$form = new WachtwoordWijzigenForm($account, 'reset?token=' . rawurlencode($token), false);
		if ($form->validate()) {
			// wachtwoord opslaan
			$pass_plain = $form->findByName('wijzigww')->getValue();
			if (AccountModel::instance()->wijzigWachtwoord($account, $pass_plain)) {
				setMelding('Wachtwoord instellen geslaagd', 1);
			}
			// token verbruikt
			// (pas na wachtwoord opslaan om meedere pogingen toe te staan als wachtwoord niet aan eisen voldoet)
			OneTimeTokensModel::instance()->discardToken($account->uid, '/wachtwoord/reset');
			// inloggen alsof gebruiker wachtwoord heeft ingevoerd
			$loggedin = $this->model->login($account->uid, $pass_plain, false);
			if (!$loggedin) {
				throw new CsrGebruikerException('Inloggen met nieuw wachtwoord mislukt');
			}
			// stuur bevestigingsmail
			$profiel = $account->getProfiel();
			require_once 'model/entity/Mail.class.php';
			$bericht = "Geachte " . $profiel->getNaam('civitas') .
				",\n\nU heeft recent uw wachtwoord opnieuw ingesteld. Als u dit niet zelf gedaan heeft dan moet u nu direct uw wachtwoord wijzigen en de PubCie op de hoogte stellen.\n\nMet amicale groet,\nUw PubCie";
			$emailNaam = $profiel->getNaam('volledig');
			$mail = new Mail(array($account->email => $emailNaam), '[C.S.R. webstek] Nieuw wachtwoord ingesteld', $bericht);
			$mail->send();
			redirect(CSR_ROOT);
		}
		if (LoginModel::mag(P_LOGGED_IN)){
			$this->view = new CsrLayoutPage($form);
		} else {
			$this->view = new CsrLayoutOweePage($form);
		}
	}

	public function wachtwoordvergeten() {
		$form = new WachtwoordVergetenForm();
		if ($form->validate()) {
			$values = $form->getValues();
			$account = AccountModel::get($values['user']);
			// mag wachtwoord reset aanvragen?
			// (mag ook als na verify($tokenString) niet ingelogd is met wachtwoord en dus AuthenticationMethod::url_token is)
			if (!$account OR !AccessModel::mag($account, P_LOGGED_IN, AuthenticationMethod::getTypeOptions()) OR mb_strtolower($account->email) !== mb_strtolower($values['mail'])) {
				setMelding('Lidnummer en/of e-mailadres onjuist', -1);
			} else {
				$token = OneTimeTokensModel::instance()->createToken($account->uid, '/wachtwoord/reset');
				// stuur resetmail
				$profiel = $account->getProfiel();
				$url =  CSR_ROOT ."/wachtwoord/reset?token=". rawurlencode($token[0]);
				require_once 'model/entity/Mail.class.php';
				$bericht = "Geachte " . $profiel->getNaam('civitas') .
					",\n\nU heeft verzocht om uw wachtwoord opnieuw in te stellen. Dit is mogelijk met de onderstaande link tot " . $token[1] .
					".\n\n[url=". $url  .
					"]Wachtwoord instellen[/url].\n\nAls dit niet uw eigen verzoek is kunt u dit bericht negeren.\n\nMet amicale groet,\nUw PubCie";
				$emailNaam = $profiel->getNaam('volledig', true); // Forceer, want gebruiker is niet ingelogd en krijgt anders 'civitas'
				$mail = new Mail(array($account->email => $emailNaam), '[C.S.R. webstek] Wachtwoord vergeten', $bericht);
				$mail->send();
				setMelding('Wachtwoord reset email verzonden', 1);
			}
		}
		$this->view = new CsrLayoutOweePage($form);
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
			$this->exit_http(403);
		}
		$deleted = $this->model->delete($session);
		$this->view = new RemoveRowsResponse(array($session), $deleted === 1 ? 200 : 404);
	}

	public function loginlockip() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (!$selection) {
			$this->exit_http(403);
		}
		$response = array();
		foreach ($selection as $UUID) {
			/** @var RememberLogin $remember */
			$remember = RememberLoginModel::instance()->retrieveByUUID($UUID);
			if (!$remember OR $remember->uid !== LoginModel::getUid()) {
				$this->exit_http(403);
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
			$remember = RememberLoginModel::instance()->retrieveByUUID($selection[0]);
		} else {
			$remember = RememberLoginModel::instance()->nieuw();
		}
		if (!$remember OR $remember->uid !== LoginModel::getUid()) {
			$this->exit_http(403);
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
			} else if (isset($_POST['redirect'])) {
				$this->view = new JsonResponse($_POST['redirect']);
			}
			else {
				$this->view = new JsonResponse(CSR_ROOT);
			}
		} else {
			$this->view = $form;
		}
	}

	public function loginforget() {
		$selection = filter_input(INPUT_POST, 'DataTableSelection', FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		if (!$selection) {
			$this->exit_http(403);
		}
		$response = array();
		foreach ($selection as $UUID) {
			/** @var RememberLogin $remember */
			$remember = RememberLoginModel::instance()->retrieveByUUID($UUID);
			if (!$remember OR $remember->uid !== LoginModel::getUid()) {
				$this->exit_http(403);
			}
			RememberLoginModel::instance()->delete($remember);
			$response[] = $remember;
		}
		$this->view = new RemoveRowsResponse($response);
	}

}
