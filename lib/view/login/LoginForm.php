<?php

namespace CsrDelft\view\login;

use CsrDelft\model\security\LoginModel;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\invoervelden\WachtwoordField;
use CsrDelft\view\formulier\keuzevelden\CheckboxField;

class LoginForm extends Formulier {

	public function __construct($showMelding = false) {
		parent::__construct(null, '/login');
		$this->formId = 'loginform';
		$this->showMelding = $showMelding;

		$fields = [];

		$redirectUri = filter_input(INPUT_GET, 'redirect', FILTER_UNSAFE_RAW);
		$fields['redirect'] = new HiddenField('redirect', $redirectUri);

		$fields['user'] = new TextField('user', null, null);
		$fields['user']->placeholder = 'Lidnummer of emailadres';

		$fields['pass'] = new WachtwoordField('pass', null, null);
		$fields['pass']->placeholder = 'Wachtwoord';

		if (LoginModel::instance()->hasError()) {
			$fields[] = new HtmlComment('<p class="error">' . LoginModel::instance()->getError() . '</p>');
		} else {
			$fields[] = new HtmlComment('<div class="float-left">');
			$fields[] = new HtmlComment('</div>');

			$fields['remember'] = new CheckboxField('remember', false, null, 'Blijf ingelogd');
		}

		$this->addFields($fields);
	}

	public function view() {
		parent::view();
		?>
		<ul class="login-buttons">
			<li>
				<a href="#" class="login-submit" onclick="document.getElementById('loginform').submit();">Inloggen</a>
				&raquo;
				&nbsp; <a href="/accountaanvragen">Account aanvragen</a> &raquo;
			</li>
			<li><a href="/wachtwoord/vergeten">Wachtwoord vergeten?</a></li>
		</ul>
		<?php
	}

}
