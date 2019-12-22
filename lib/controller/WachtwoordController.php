<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\model\entity\Mail;
use CsrDelft\model\entity\security\AuthenticationMethod;
use CsrDelft\model\security\AccessModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\model\security\OneTimeTokensModel;
use CsrDelft\view\login\WachtwoordVergetenForm;
use CsrDelft\view\login\WachtwoordWijzigenForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 28/07/2019
 */
class WachtwoordController extends AbstractController {
	/**
	 * @var LoginModel
	 */
	private $loginModel;
	/**
	 * @var AccountModel
	 */
	private $accountModel;
	/**
	 * @var OneTimeTokensModel
	 */
	private $oneTimeTokensModel;

	public function __construct(LoginModel $loginModel, AccountModel $accountModel, OneTimeTokensModel $oneTimeTokensModel) {
		$this->loginModel = $loginModel;
		$this->accountModel = $accountModel;
		$this->oneTimeTokensModel = $oneTimeTokensModel;
	}

	public function wijzigen() {
		$account = LoginModel::getAccount();
		// mag inloggen?
		if (!$account OR !AccessModel::mag($account, P_LOGGED_IN)) {
			throw new CsrToegangException();
		}
		$form = new WachtwoordWijzigenForm($account, 'wijzigen');
		if ($form->validate()) {
			// wachtwoord opslaan
			$pass_plain = $form->findByName('wijzigww')->getValue();
			$this->accountModel->wijzigWachtwoord($account, $pass_plain);
			setMelding('Wachtwoord instellen geslaagd', 1);
		}
		return view('default', ['content' => $form]);
	}

	public function reset() {
		$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
		$account = $this->oneTimeTokensModel->verifyToken('/wachtwoord/reset', $token);

		if ($account == null) {
			throw new CsrToegangException();
		}
		$form = new WachtwoordWijzigenForm($account, 'reset?token=' . rawurlencode($token), false);
		if ($form->validate()) {
			// wachtwoord opslaan
			$pass_plain = $form->findByName('wijzigww')->getValue();
			if ($this->accountModel->wijzigWachtwoord($account, $pass_plain)) {
				setMelding('Wachtwoord instellen geslaagd', 1);
			}
			// token verbruikt
			// (pas na wachtwoord opslaan om meedere pogingen toe te staan als wachtwoord niet aan eisen voldoet)
			$this->oneTimeTokensModel->discardToken($account->uid, '/wachtwoord/reset');
			// inloggen alsof gebruiker wachtwoord heeft ingevoerd
			$loggedin = $this->loginModel->login($account->uid, $pass_plain, false);
			if (!$loggedin) {
				throw new CsrGebruikerException('Inloggen met nieuw wachtwoord mislukt');
			}
			// stuur bevestigingsmail
			$profiel = $account->getProfiel();
			$bericht = "Geachte " . $profiel->getNaam('civitas') .
				",\n\nU heeft recent uw wachtwoord opnieuw ingesteld. Als u dit niet zelf gedaan heeft dan moet u nu direct uw wachtwoord wijzigen en de PubCie op de hoogte stellen.\n\nMet amicale groet,\nUw PubCie";
			$emailNaam = $profiel->getNaam('volledig');
			$mail = new Mail(array($account->email => $emailNaam), '[C.S.R. webstek] Nieuw wachtwoord ingesteld', $bericht);
			$mail->send();
			return $this->redirectToRoute('default');
		}
		return view('default', ['content' => $form]);
	}

	public function vergeten() {
		$form = new WachtwoordVergetenForm();
		if ($form->validate()) {
			$values = $form->getValues();
			$account = AccountModel::get($values['user']);
			// mag wachtwoord reset aanvragen?
			// (mag ook als na verify($tokenString) niet ingelogd is met wachtwoord en dus AuthenticationMethod::url_token is)
			if (!$account OR !AccessModel::mag($account, P_LOGGED_IN, AuthenticationMethod::getTypeOptions()) OR mb_strtolower($account->email) !== mb_strtolower($values['mail'])) {
				setMelding('Lidnummer en/of e-mailadres onjuist', -1);
			} else {
				$token = $this->oneTimeTokensModel->createToken($account->uid, '/wachtwoord/reset');
				// stuur resetmail
				$profiel = $account->getProfiel();
				$url =  CSR_ROOT ."/wachtwoord/reset?token=". rawurlencode($token[0]);
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
		return view('default', ['content' => $form]);
	}
}
