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

		$fields['url'] = new UrlField('url', HTTP_REFERER);
		$fields['url']->hidden = true;

		$this->addFields($fields);
	}

}
