<?php

namespace CsrDelft\controller;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\common\CsrToegangException;
use CsrDelft\model\CmsPaginaModel;
use CsrDelft\model\entity\security\AuthenticationMethod;
use CsrDelft\model\security\AccessModel;
use CsrDelft\model\security\AccountModel;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\CsrLayoutPage;
use CsrDelft\view\JsonResponse;
use CsrDelft\view\login\AccountForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 28/07/2019
 */
class AccountController {
	public function aanvragen() {
		return view('default', ['content' => CmsPaginaModel::get('accountaanvragen')]);
	}

	public function aanmaken($uid = null) {
		if (!LoginModel::mag(P_ADMIN)) {
			throw new CsrToegangException();
		}
		if ($uid == null) {
			$uid = LoginModel::instance()->getUid();
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

	public function bewerken($uid = null) {
		if ($uid == null) {
			$uid = LoginModel::instance()->getUid();
		}
		if ($uid === 'x999') {
			return $this->aanvragen();
		}
		if ($uid !== LoginModel::instance()->getUid() && !LoginModel::mag(P_ADMIN)) {
			throw new CsrToegangException();
		}
		if (LoginModel::instance()->getAuthenticationMethod() !== AuthenticationMethod::recent_password_login) {
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
			AccountModel::instance()->wijzigWachtwoord($account, $pass_plain);
			setMelding('Inloggegevens wijzigen geslaagd', 1);
		}
		return new CsrLayoutPage($form);
	}

	public function verwijderen($uid = null) {
		if ($uid == null) {
			$uid = LoginModel::instance()->getUid();
		}
		if ($uid !== LoginModel::instance()->getUid() && !LoginModel::mag(P_ADMIN)) {
			throw new CsrToegangException();
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
		return new JsonResponse('/profiel/' . $uid); // redirect
	}
}
