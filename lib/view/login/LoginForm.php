<?php

namespace CsrDelft\view\login;

use CsrDelft\view\formulier\CsrfField;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\invoervelden\WachtwoordField;
use CsrDelft\view\formulier\keuzevelden\CheckboxField;
use CsrDelft\view\formulier\knoppen\LoginFormKnoppen;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;

/**
 * Class LoginForm
 * @package CsrDelft\view\login
 * @see FormLoginAuthenticator
 */
class LoginForm extends Formulier {

	/**
	 * @var UrlGeneratorInterface
	 */
	private $urlGenerator;

	public function __construct(UrlGeneratorInterface $urlGenerator, $csrfToken, $error, $showMelding = false) {
		parent::__construct(null, $urlGenerator->generate('app_login_check'));
		$this->formId = 'loginform';
		$this->showMelding = $showMelding;

		$fields = [];

		$redirectUri = filter_input(INPUT_GET, 'redirect', FILTER_UNSAFE_RAW);
		$fields['redirect'] = new HiddenField('redirect', $redirectUri);

		$fields[] = new CsrfField($csrfToken, '_csrf_token');

		$fields['user'] = new TextField('_username', null, null);
		$fields['user']->placeholder = 'Lidnummer of emailadres';

		$fields['pass'] = new WachtwoordField('_password', null, null);
		$fields['pass']->placeholder = 'Wachtwoord';

		if ($error) {
			$fields[] = new HtmlComment('<p class="error">' . $error . '</p>');
		} else {
			$fields[] = new HtmlComment('<div class="float-left">');
			$fields[] = new HtmlComment('</div>');

			$fields['remember'] = new CheckboxField('_remember_me', false, null, 'Blijf ingelogd');
		}

		$this->addFields($fields);
		$this->urlGenerator = $urlGenerator;

		$this->formKnoppen = new LoginFormKnoppen();
	}

	protected function getScriptTag() {
		// er is geen javascript
		return "";
	}
}
