<?php

namespace CsrDelft\view\login;

use CsrDelft\common\ContainerFacade;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\CsrfField;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\invoervelden\WachtwoordField;
use CsrDelft\view\formulier\keuzevelden\CheckboxField;
use CsrDelft\view\formulier\knoppen\LoginFormKnoppen;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;

/**
 * Class LoginForm
 * @package CsrDelft\view\login
 * @see FormLoginAuthenticator
 */
class LoginForm extends Formulier {

	public function __construct($showMelding = false) {
		parent::__construct(null, '/login_check');
		$this->formId = 'loginform';
		$this->showMelding = $showMelding;

		$fields = [];

		$fields[] = new CsrfField(ContainerFacade::getContainer()->get('security.csrf.token_manager')->getToken('authenticate'), '_csrf_token');

		$fields['user'] = new TextField('_username', null, null);
		$fields['user']->placeholder = 'Lidnummer of emailadres';

		$fields['pass'] = new WachtwoordField('_password', null, null);
		$fields['pass']->placeholder = 'Wachtwoord';

		if (ContainerFacade::getContainer()->get(LoginService::class)->hasError()) {
			$fields[] = new HtmlComment('<p class="error">' . ContainerFacade::getContainer()->get(LoginService::class)->getError() . '</p>');
		} else {
			$fields[] = new HtmlComment('<div class="float-left">');
			$fields[] = new HtmlComment('</div>');

			$fields['remember'] = new CheckboxField('_remember_me', false, null, 'Blijf ingelogd');
		}

		$this->addFields($fields);

		$this->formKnoppen = new LoginFormKnoppen();
	}

	protected function getScriptTag() {
		// er is geen javascript
		return "";
	}
}
