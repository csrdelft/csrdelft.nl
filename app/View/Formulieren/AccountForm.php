<?php

namespace App\View\Formulieren;

use App\Models\Account;
use CsrDelft\model\entity\security\AccessRole;
use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\RequiredEmailField;
use CsrDelft\view\formulier\invoervelden\UsernameField;
use CsrDelft\view\formulier\invoervelden\WachtwoordWijzigenField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\DeleteKnop;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use Illuminate\Support\Facades\Auth;

class AccountForm extends Formulier {

	public function __construct(Account $account) {
		parent::__construct($account, '/account/' . $account->uid . '/bewerken', 'Inloggegevens aanpassen');

		if (LoginModel::mag('P_LEDEN_MOD')) {
			$roles = array();
			foreach (AccessRole::canChangeAccessRoleTo(Auth::user()->perm_role) as $optie) {
				$roles[$optie] = AccessRole::getDescription($optie);
			}
			$fields[] = new SelectField('perm_role', $account->perm_role, 'Rechten', $roles);
		}

		$fields[] = new UsernameField('username', $account->username);
		$fields[] = new RequiredEmailField('email', $account->email, 'E-mailadres');
		$fields[] = new WachtwoordWijzigenField('wijzigww', $account, !LoginModel::mag('P_LEDEN_MOD'));
		$fields['btn'] = new FormDefaultKnoppen('/profiel/' . $account->uid, false, true, true, true);

		$delete = new DeleteKnop('/account/' . $account->uid . '/delete');
		$fields['btn']->addKnop($delete, true);

		$this->addFields($fields);
	}

}
