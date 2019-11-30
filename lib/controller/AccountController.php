<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\model\entity\security\AuthenticationMethod;
use CsrDelft\model\security\AccessModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\login\AccountForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 28/07/2019
 */
class AccountController extends AbstractController {
	/**
	 * @var CmsPaginaRepository
	 */
	private $cmsPaginaRepository;
	/**
	 * @var AccountModel
	 */
	private $accountModel;
	/**
	 * @var LoginModel
	 */
	private $loginModel;

	public function __construct(AccountModel $accountModel, LoginModel $loginModel, CmsPaginaRepository $cmsPaginaRepository) {
		$this->cmsPaginaRepository = $cmsPaginaRepository;
		$this->accountModel = $accountModel;
		$this->loginModel = $loginModel;
	}

	public function aanvragen() {
		return view('default', ['content' => $this->cmsPaginaRepository->find('accountaanvragen')]);
	}

	public function aanmaken($uid = null) {
		if (!LoginModel::mag(P_ADMIN)) {
			throw new CsrToegangException();
		}
		if ($uid == null) {
			$uid = $this->loginModel->getUid();
		}
		if (AccountModel::get($uid)) {
			setMelding('Account bestaat al', 0);
		} else {
			$account = $this->accountModel->maakAccount($uid);
			if ($account) {
				setMelding('Account succesvol aangemaakt', 1);
			} else {
				throw new CsrGebruikerException('Account aanmaken gefaald');
			}
		}
		return $this->redirectToRoute('account-bewerken', ['uid' => $uid]);
	}

	public function bewerken($uid = null) {
		if ($uid == null) {
			$uid = $this->loginModel->getUid();
		}
		if ($uid === 'x999') {
			return $this->aanvragen();
		}
		if ($uid !== $this->loginModel->getUid() && !LoginModel::mag(P_ADMIN)) {
			throw new CsrToegangException();
		}
		if ($this->loginModel->getAuthenticationMethod() !== AuthenticationMethod::recent_password_login) {
			setMelding('U mag geen account wijzigen want u bent niet recent met wachtwoord ingelogd', 2);
			throw new CsrToegangException();
		}
		$account = AccountModel::get($uid);
		if (!$account) {
			setMelding('Account bestaat niet', -1);
			throw new CsrToegangException();
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
			$this->accountModel->wijzigWachtwoord($account, $pass_plain);
			setMelding('Inloggegevens wijzigen geslaagd', 1);
		}
		return view('default', ['content' => $form]);
	}

	public function verwijderen($uid = null) {
		if ($uid == null) {
			$uid = $this->loginModel->getUid();
		}
		if ($uid !== $this->loginModel->getUid() && !LoginModel::mag(P_ADMIN)) {
			throw new CsrToegangException();
		}
		$account = AccountModel::get($uid);
		if (!$account) {
			setMelding('Account bestaat niet', -1);
		} else {
			$result = $this->accountModel->delete($account);
			if ($result === 1) {
				setMelding('Account succesvol verwijderd', 1);
			} else {
				setMelding('Account verwijderen mislukt', -1);
			}
		}
		return new JsonResponse('/profiel/' . $uid); // redirect
	}
}
