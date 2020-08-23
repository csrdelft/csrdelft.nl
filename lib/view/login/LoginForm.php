<?php

namespace CsrDelft\view\login;

use CsrDelft\common\ContainerFacade;
use CsrDelft\view\formulier\CsrfField;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\invoervelden\WachtwoordField;
use CsrDelft\view\formulier\keuzevelden\CheckboxField;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use CsrDelft\view\formulier\knoppen\LoginFormKnoppen;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;

/**
 * Class LoginForm
 * @package CsrDelft\view\login
 * @see FormLoginAuthenticator Voor de afhandeling van dit formulier
 */
class LoginForm extends Formulier {

	public function __construct($lastUserName = null, AuthenticationException $lastError = null) {
		parent::__construct(null, '/login_check');
		$this->formId = 'loginform';
		$this->showMelding = false;

		$fields = [];

		$fields[] = new CsrfField(ContainerFacade::getContainer()->get('security.csrf.token_manager')->getToken('authenticate'), '_csrf_token');

		$fields['user'] = new TextField('_username', $lastUserName, null);
		$fields['user']->placeholder = 'Lidnummer of emailadres';

		$fields['pass'] = new WachtwoordField('_password', null, null);
		$fields['pass']->placeholder = 'Wachtwoord';

		if ($lastError) {
			$fields[] = new HtmlComment('<p class="error">' . $this->formatError($lastError) . '</p>');
		} else {
			$fields[] = new HtmlComment('<div class="float-left">');
			$fields[] = new HtmlComment('</div>');

			$fields['remember'] = new CheckboxField('_remember_me', false, null, 'Blijf ingelogd');
		}

		$this->addFields($fields);

		$this->formKnoppen = new LoginFormKnoppen();
	}

	/**
	 * Bij gebrek aan standaard vertalingen.
	 *
	 * @param AuthenticationException $exception
	 * @return string
	 */
	private function formatError(AuthenticationException $exception) {
		switch ($exception->getMessageKey()) {
			case "Username could not be found.":
				$errorString = "Gebruiker {{ username }} niet gevonden.";
				break;
			case "Invalid credentials.":
				$errorString = "Onjuist wachtwoord.";
				break;
			default:
				$errorString = "Er was een fout.";
				break;
		}

		return strtr($errorString, $exception->getMessageData());
	}

	protected function getScriptTag() {
		// er is geen javascript
		return "";
	}
}
