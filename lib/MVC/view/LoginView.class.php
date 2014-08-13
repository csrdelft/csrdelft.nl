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

		$fields[] = new VinkField('koppelip', true, 'Koppel IP');

		$url = filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL);
		$fields[] = new HiddenField('url', $url);

		$this->addFields($fields);
	}

}
