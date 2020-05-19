<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\model\entity\security\AuthenticationMethod;
use CsrDelft\repository\CmsPaginaRepository;
use CsrDelft\repository\security\AccountRepository;
use CsrDelft\service\AccessService;
use CsrDelft\service\security\LoginService;
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
	 * @var AccountRepository
	 */
	private $accountRepository;
	/**
	 * @var LoginService
	 */
	private $loginService;

	public function __construct(AccountRepository $accountRepository, LoginService $loginService, CmsPaginaRepository $cmsPaginaRepository) {
		$this->cmsPaginaRepository = $cmsPaginaRepository;
		$this->accountRepository = $accountRepository;
		$this->loginService = $loginService;
	}

	public function aanvragen() {
		return view('default', ['content' => $this->cmsPaginaRepository->find('accountaanvragen')]);
	}

	public function aanmaken($uid = null) {
		if (!LoginService::mag(P_ADMIN)) {
			throw new CsrToegangException();
		}
		if ($uid == null) {
			$uid = $this->loginService->getUid();
		}
		if ($this->accountRepository->get($uid)) {
			setMelding('Account bestaat al', 0);
		} else {
			$account = $this->accountRepository->maakAccount($uid);
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
			$uid = $this->loginService->getUid();
		}
		if ($uid === LoginService::UID_EXTERN) {
			return $this->aanvragen();
		}
		if ($uid !== $this->loginService->getUid() && !LoginService::mag(P_ADMIN)) {
			throw new CsrToegangException();
		}
		if ($this->loginService->getAuthenticationMethod() !== AuthenticationMethod::recent_password_login) {
			setMelding('U mag geen account wijzigen want u bent niet recent met wachtwoord ingelogd', 2);
			throw new CsrToegangException();
		}
		$account = $this->accountRepository->get($uid);
		if (!$account) {
			setMelding('Account bestaat niet', -1);
			throw new CsrToegangException();
		}
		if (!AccessService::mag($account, P_LOGGED_IN)) {
			setMelding('Account mag niet inloggen', 2);
		}
		$form = new AccountForm($account);
		if ($form->validate()) {
			if ($form->findByName('username')->getValue() == '') {
				$account->username = $account->uid;
			}
			// username, email & wachtwoord opslaan
			$pass_plain = $form->findByName('wijzigww')->getValue();
			$this->accountRepository->wijzigWachtwoord($account, $pass_plain);
			setMelding('Inloggegevens wijzigen geslaagd', 1);
		}
		return view('default', ['content' => $form]);
	}

	public function verwijderen($uid = null) {
		if ($uid == null) {
			$uid = $this->loginService->getUid();
		}
		if ($uid !== $this->loginService->getUid() && !LoginService::mag(P_ADMIN)) {
			throw new CsrToegangException();
		}
		$account = $this->accountRepository->get($uid);
		if (!$account) {
			setMelding('Account bestaat niet', -1);
		} else {
			$result = $this->accountRepository->delete($account);
			if ($result === 1) {
				setMelding('Account succesvol verwijderd', 1);
			} else {
				setMelding('Account verwijderen mislukt', -1);
			}
		}
		return new JsonResponse('/profiel/' . $uid); // redirect
	}
}
