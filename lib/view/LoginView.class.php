<?php

/**
 * LoginView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van login sessies en diverse formulieren.
 */
class SessionsView extends DataTable implements FormElement {

	public function __construct() {
		parent::__construct(LoginModel::orm, 'Sessiebeheer', 'ip');
		$this->dataUrl = '/sessions';
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

class SessionsData extends DataTableResponse {

	public function __construct($uid) {
		parent::__construct(LoginModel::instance()->find('uid = ?', array($uid)));
	}

	public function getJson($sessie) {
		$array = $sessie->jsonSerialize();

		$array['details'] = '<a href="/endsession/' . $array['session_id'] . '" class="post DataTableResponse" title="Log uit"><img width="16" height="16" class="icon" src="/plaetjes/famfamfam/door_in.png"></a>';

		return parent::getJson($array);
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

	public function __construct($tokenValue) {
		parent::__construct(null, 'verifyform', '/verify/' . $tokenValue, 'Verifieren');

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

		$fields[] = new WachtwoordWijzigenField('wwreset', $account, $require_current);
		$fields[] = new FormDefaultKnoppen('/', false, true, true, true);
		$fields[] = new HtmlComment('<img src="http://imgs.xkcd.com/comics/password_strength.png" title="http://xkcd.com/936/" style="margin-top: 50px;" />');

		$this->addFields($fields);
	}

}
