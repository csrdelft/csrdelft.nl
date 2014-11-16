<?php

/**
 * LoginView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van het login formulier.
 */
class LoginForm extends Formulier {

	public function __construct() {
		parent::__construct(null, 'loginform', '/login');

		$fields['user'] = new TextField('user');
		$fields['user']->placeholder = 'Bijnaam of lidnummer';

		$fields['pass'] = new WachtwoordField('pass');
		$fields['pass']->placeholder = 'Wachtwoord';

		$fields['pauper'] = new VinkField('mobiel', LoginModel::instance()->isPauper(), null, 'Mobiel');
		$fields['pauper']->onchange = 'this.form.submit();';

		$fields['url'] = new UrlField('url', HTTP_REFERER);
		$fields['url']->hidden = true;

		$this->addFields($fields);
	}

}

class VerifyForm extends Formulier {

	public function __construct($tokenValue) {
		parent::__construct(null, 'verifyform', '/verify/' . $tokenValue, 'Verifieren');

		$fields[] = new RequiredTextField('user', null, 'Lidnummer');
		$fields[] = new FormKnoppen(CSR_ROOT, true, true, false, true);

		$this->addFields($fields);
	}

}

class WachtwoordVergetenForm extends Formulier {

	public function __construct() {
		parent::__construct(null, 'wwvergetenform', '/wachtwoord/vergeten', 'Wachtwoord vergeten');

		$fields[] = new RequiredTextField('user', null, 'Lidnummer');
		$fields[] = new RequiredEmailField('mail', null, 'E-mail adres');
		$fields[] = new FormKnoppen(CSR_ROOT, true, true, false, true);

		$this->addFields($fields);
	}

}

class WachtwoordResetForm extends Formulier {

	public function __construct(Lid $lid) {
		parent::__construct($lid, 'wwresetform', '/wachtwoord/reset', 'Wachtwoord instellen');

		$fields[] = new WachtwoordWijzigenField('wwreset', $lid, true);
		$fields[] = new FormKnoppen(CSR_ROOT, true, true, false, true);

		$this->addFields($fields);
	}

}
