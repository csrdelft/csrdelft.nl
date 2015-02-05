<?php

require_once 'model/entity/security/RememberLogin.class.php';

/**
 * LoginView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van login sessies en diverse formulieren.
 */
class LoginSessionsTable extends DataTable implements FormElement {

	public function __construct() {
		parent::__construct(LoginModel::orm, 'Sessiebeheer', 'ip');
		$this->settings['tableTools']['aButtons'] = array();
		$this->dataUrl = '/loginsessionsdata';
		$this->hideColumn('uid');
		$this->searchColumn('login_moment');
		$this->searchColumn('user_agent');
	}

	public function getHtml() {
		throw new Exception('unsupported');
	}

	public function getType() {
		return get_class($this);
	}

}

class LoginSessionsData extends DataTableResponse {

	public function getJson($session) {
		$array = $session->jsonSerialize();

		$array['details'] = '<a href="/loginendsession/' . $session->session_hash . '" class="post DataTableResponse SingleRow" title="Log uit"><img width="16" height="16" class="icon" src="/plaetjes/famfamfam/door_in.png"></a>';

		$array['login_moment'] = reldate($array['login_moment']);

		if ($session->lock_ip) {
			$array['lock_ip'] = '<img width="16" height="16" class="icon" src="/plaetjes/famfamfam/lock.png" title="Gekoppeld aan IP-adres">';
		} else {
			$array['lock_ip'] = null;
		}

		return parent::getJson($array);
	}

}

class RememberLoginTable extends DataTable implements FormElement {

	public function __construct() {
		parent::__construct(RememberLoginModel::orm, 'Automatisch inloggen', 'ip');
		$this->settings['tableTools']['aButtons'] = array();
		$this->dataUrl = '/loginrememberdata';
		$this->hideColumn('token');
		$this->hideColumn('uid');
		$this->searchColumn('remember_since');
		$this->searchColumn('device_name');

		$create = new DataTableKnop('== 0', $this->tableId, '/loginremember', 'post popup', 'Toevoegen', 'Automatisch inloggen vanaf dit apparaat', 'add');
		$this->addKnop($create);

		$update = new DataTableKnop('== 1', $this->tableId, '/loginremember', 'post popup', 'Naam wijzigen', 'Wijzig naam van apparaat', 'edit');
		$this->addKnop($update);

		$lock = new DataTableKnop('>= 1', $this->tableId, '/loginlockip', 'post', '(Ont)Koppel IP', 'Alleen inloggen vanaf bepaald IP-adres', 'lock');
		$this->addKnop($lock);

		$delte = new DataTableKnop('>= 1', $this->tableId, '/loginforget', 'post', 'Verwijderen', 'Stop automatische login voor dit apparaat', 'delete');
		$this->addKnop($delte);
	}

	public function getHtml() {
		throw new Exception('unsupported');
	}

	public function getType() {
		return get_class($this);
	}

}

class RememberLoginData extends DataTableResponse {

	public function getJson($remember) {
		$array = $remember->jsonSerialize();

		$array['token'] = null; // keep it private

		$array['remember_since'] = reldate($array['remember_since']);

		if ($remember->lock_ip) {
			$array['lock_ip'] = '<img width="16" height="16" class="icon" src="/plaetjes/famfamfam/lock.png" title="Gekoppeld aan IP-adres">';
		} else {
			$array['lock_ip'] = '';
		}

		return parent::getJson($array);
	}

}

class RememberLoginForm extends DataTableForm {

	public function __construct(RememberLogin $remember) {
		parent::__construct($remember, '/loginremember', 'Automatisch inloggen vanaf huidig apparaat');

		$fields[] = new HtmlComment('<div class="dikgedrukt">Gebruik deze functie alleen voor een veilig apparaat op een veilige locatie.</div>');
		$fields[] = new RequiredTextField('device_name', $remember->device_name, 'Naam apparaat');
		$fields[] = new FormDefaultKnoppen('/', false, true, true, true, true);

		$this->addFields($fields);
	}

}

class RememberAfterLoginForm extends RememberLoginForm {

	public function __construct(RememberLogin $remember) {
		parent::__construct($remember);
		$this->tableId = false; // same as parent but without data table
	}

}

class LoginForm extends Formulier {

	public function __construct() {
		parent::__construct(null, '/login');
		$this->formId = 'loginform';

		$fields['user'] = new TextField('user', null, null);
		$fields['user']->placeholder = 'Bijnaam of lidnummer';

		$fields['pass'] = new WachtwoordField('pass', null, null);
		$fields['pass']->placeholder = 'Wachtwoord';

		if (LoginModel::instance()->hasError()) {
			$fields[] = new HtmlComment('<p class="error">' . LoginModel::instance()->getError() . '</p>');
		} else {
			$fields['remember'] = new CheckboxField('remember', false, null, 'Blijf ingelogd');
		}

		$this->addFields($fields);
	}

}

class VerifyForm extends Formulier {

	public function __construct($tokenString) {
		parent::__construct(null, '/verify/' . $tokenString, 'Verifieren');

		$fields[] = new RequiredTextField('user', null, 'Lidnummer');
		$fields[] = new FormDefaultKnoppen('/', false, true, true, true);

		$this->addFields($fields);
	}

}

class WachtwoordVergetenForm extends Formulier {

	public function __construct() {
		parent::__construct(null, '/wachtwoord/vergeten', 'Wachtwoord vergeten');

		$fields[] = new RequiredTextField('user', null, 'Lidnummer');
		$fields[] = new RequiredEmailField('mail', null, 'E-mailadres');
		$fields[] = new FormDefaultKnoppen('/', false, true, true, true);

		$this->addFields($fields);
	}

}

class WachtwoordWijzigenForm extends Formulier {

	public function __construct(Account $account, $action, $require_current = true) {
		parent::__construct($account, '/wachtwoord/' . $action, 'Wachtwoord instellen');

		if ($account->email == '') {
			setMelding('Vul uw e-mailadres in om uw wachtwoord te kunnen resetten als u deze bent vergeten.', 0);
			$fields[] = new RequiredEmailField('email', $account->email, 'E-mailadres');
		}
		$fields[] = new RequiredWachtwoordWijzigenField('wijzigww', $account, $require_current);
		$fields[] = new FormDefaultKnoppen('/', false, true, true, true);
		$fields[] = new HtmlComment('<img src="http://imgs.xkcd.com/comics/password_strength.png" title="http://xkcd.com/936/" style="margin-top: 50px;">');

		$this->addFields($fields);
	}

}

class AccountForm extends Formulier {

	public function __construct(Account $account) {
		parent::__construct($account, '/account/' . $account->uid, 'Inloggegevens aanpassen');

		if (LoginModel::mag('P_LEDEN_MOD')) {
			$roles = array();
			foreach (AccessRole::getTypeOptions() as $optie) {
				$roles[$optie] = AccessRole::getDescription($optie);
			}
			$fields[] = new SelectField('perm_role', $account->perm_role, 'Rechten', $roles);
		}

		$fields[] = new UsernameField('username', $account->username);
		$fields[] = new RequiredEmailField('email', $account->email, 'E-mailadres');
		$fields[] = new WachtwoordWijzigenField('wijzigww', $account, true);
		$fields['btn'] = new FormDefaultKnoppen('/', false, true, true, true);

		$delete = new DeleteKnop($this->action . '/delete');
		$fields['btn']->addKnop($delete, true);

		$this->addFields($fields);
	}

}
