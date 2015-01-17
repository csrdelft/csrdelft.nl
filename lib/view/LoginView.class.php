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
		$this->hideColumn('lock_ip');
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

		$array['details'] = '<a href="/loginendsession/' . $session->session_id . '" class="post DataTableResponse" title="Log uit"><img width="16" height="16" class="icon" src="/plaetjes/famfamfam/door_in.png"></a>';

		if ($session->lock_ip) {
			$array['details'] .= '<img width="16" height="16" class="icon" src="/plaetjes/famfamfam/lock.png" title="Gekoppeld aan IP-adres">';
		}

		return parent::getJson($array);
	}

}

class RememberLoginTable extends DataTable implements FormElement {

	public function __construct() {
		parent::__construct(RememberLoginModel::orm, 'Automatisch inloggen', 'ip');
		$this->settings['tableTools']['aButtons'] = array();
		$this->dataUrl = '/loginrememberdata';
		$this->hideColumn('uid');
		$this->hideColumn('lock_ip');
		$this->searchColumn('remember_since');
		$this->searchColumn('device_name');

		$nieuw = new DataTableKnop('>= 0', '/loginremember', 'post popup', 78, 'Toevoegen', 'Automatisch inloggen vanaf dit apparaat (Sneltoets: N)', '/famfamfam/add.png');
		$this->addKnop($nieuw);

		$wijzig = new DataTableKnop('== 1', '/loginremember', 'post popup', 87, 'Naam wijzigen', 'Wijzig naam van apparaat (Sneltoets: W)', '/famfamfam/pencil.png');
		$this->addKnop($wijzig);

		$lock = new DataTableKnop('== 1', '/loginlockip', 'post', 76, '(Ont)Koppel IP', 'Alleen inloggen vanaf bepaald IP-adres (Sneltoets: L)', '/famfamfam/lock.png');
		$this->addKnop($lock);

		$forget = new DataTableKnop('== 1', '/loginforget', 'post', null, 'Verwijderen', 'Stop automatische login voor dit apparaat', '/famfamfam/cross.png');
		$this->addKnop($forget);
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

		if ($remember->lock_ip) {
			$array['details'] = '<img width="16" height="16" class="icon" src="/plaetjes/famfamfam/lock.png" title="Gekoppeld aan IP-adres">';
		}

		return parent::getJson($array);
	}

}

class RememberLoginForm extends ModalForm {

	public function __construct(RememberLogin $remember) {
		parent::__construct($remember, 'rememberform', '/loginremember', 'Automatisch inloggen vanaf huidig apparaat');
		$this->css_classes[] = 'DataTableResponse';

		$fields[] = new RequiredTextField('device_name', $remember->device_name, 'Naam apparaat');
		$fields[] = new FormDefaultKnoppen('/', false, true, true, true, true);

		$this->addFields($fields);
	}

}

class LoginForm extends Formulier {

	public function __construct() {
		parent::__construct(null, 'loginform', '/login');

		$fields['user'] = new TextField('user', null, null);
		$fields['user']->placeholder = 'Bijnaam of lidnummer';

		$fields['pass'] = new WachtwoordField('pass', null, null);
		$fields['pass']->placeholder = 'Wachtwoord';

		$fields['pauper'] = new VinkField('mobiel', LoginModel::instance()->isPauper(), null, 'Mobiel');
		$fields['pauper']->onchange = 'this.form.submit();';

		$fields['url'] = new UrlField('url', HTTP_REFERER, null);
		$fields['url']->hidden = true;

		$this->addFields($fields);
	}

}

class VerifyForm extends Formulier {

	public function __construct($tokenString) {
		parent::__construct(null, 'verifyform', '/verify/' . $tokenString, 'Verifieren');

		$fields[] = new RequiredTextField('user', null, 'Lidnummer');
		$fields[] = new FormDefaultKnoppen('/', false, true, true, true);

		$this->addFields($fields);
	}

}

class WachtwoordVergetenForm extends Formulier {

	public function __construct() {
		parent::__construct(null, 'wwvergetenform', '/wachtwoord/vergeten', 'Wachtwoord vergeten');

		$fields[] = new RequiredTextField('user', null, 'Lidnummer');
		$fields[] = new RequiredEmailField('mail', null, 'E-mail adres');
		$fields[] = new FormDefaultKnoppen('/', false, true, true, true);

		$this->addFields($fields);
	}

}

class WachtwoordWijzigenForm extends Formulier {

	public function __construct(Account $account, $action, $require_current = true) {
		parent::__construct($account, 'wwwijzigenform', '/wachtwoord/' . $action, 'Wachtwoord instellen');

		if ($account->email == '') {
			setMelding('Vul uw e-mailadres in om uw wachtwoord te kunnen resetten als u deze bent vergeten.', 0);
			$fields[] = new RequiredEmailField('email', $account->email, 'E-mailadres');
		}
		$fields[] = new RequiredWachtwoordWijzigenField('wijzigww', $account, $require_current);
		$fields[] = new FormDefaultKnoppen('/', false, true, true, true);
		$fields[] = new HtmlComment('<img src="http://imgs.xkcd.com/comics/password_strength.png" title="http://xkcd.com/936/" style="margin-top: 50px;" />');

		$this->addFields($fields);
	}

}

class AccountForm extends Formulier {

	public function __construct(Account $account) {
		parent::__construct($account, 'accountForm', '/account/' . $account->uid, 'Inloggegevens aanpassen');

		if (LoginModel::mag('P_LEDEN_MOD')) {
			$roles = array();
			foreach (AccessRoles::getTypeOptions() as $optie) {
				$roles[$optie] = AccessRoles::getDescription($optie);
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
