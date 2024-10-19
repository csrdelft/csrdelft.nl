<?php

namespace CsrDelft\view\login;

use CsrDelft\Component\Formulier\FormulierBuilder;
use CsrDelft\Component\Formulier\FormulierTypeInterface;
use CsrDelft\view\formulier\CsrfField;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\invoervelden\WachtwoordField;
use CsrDelft\view\formulier\keuzevelden\CheckboxField;
use CsrDelft\view\formulier\knoppen\TemplateFormKnoppen;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Class LoginForm
 * @package CsrDelft\view\login
 * @see FormLoginAuthenticator Voor de afhandeling van dit formulier
 */
class LoginForm implements FormulierTypeInterface
{
	public function __construct(
		private readonly TranslatorInterface $translator,
		private readonly UrlGeneratorInterface $urlGenerator,
		private readonly CsrfTokenManagerInterface $csrfTokenManager,
		private readonly Environment $twig
	) {
	}

	/**
	 * Bij gebrek aan standaard vertalingen.
	 *
	 * @param AuthenticationException $exception
	 * @return string
	 */
	private function formatError(
		AuthenticationException $exception,
		$lastUsername
	) {
		$errorString = match ($exception->getMessageKey()) {
			'Username could not be found.' => $this->translator->trans(
				"Gebruiker '%username%' niet gevonden.",
				['%username%' => $lastUsername]
			),
			'Invalid credentials.' => $this->translator->trans('Onjuist wachtwoord.'),
			default => $this->translator->trans('Er was een fout.'),
		};

		return strtr($errorString, $exception->getMessageData());
	}

	protected function getScriptTag()
	{
		// er is geen javascript
		return '';
	}

	public function createFormulier(
		FormulierBuilder $builder,
		$data,
		$options = []
	) {
		$builder->setAction($this->urlGenerator->generate('app_login_check'));

		$builder->setFormId('loginform');
		$builder->setShowMelding(false);

		$fields = [];

		$fields[] = new CsrfField(
			$this->csrfTokenManager->getToken('authenticate'),
			'_csrf_token'
		);

		$fields['user'] = new TextField(
			'_username',
			$options['lastUserName'] ?? '',
			null
		);
		$fields['user']->placeholder = $this->translator->trans(
			'Lidnummer of emailadres'
		);

		$fields['pass'] = new WachtwoordField('_password', null, null);
		$fields['pass']->placeholder = $this->translator->trans('Wachtwoord');

		if (isset($options['lastError'])) {
			$fields[] = new HtmlComment(
				sprintf(
					"<p class=\"error\">%s</p>",
					$this->formatError(
						$options['lastError'],
						$options['lastUserName'] ?? ''
					)
				)
			);
		} else {
			$fields[] = new HtmlComment('<div class="float-start">');
			$fields[] = new HtmlComment('</div>');

			$fields['remember'] = new CheckboxField(
				'_remember_me',
				false,
				null,
				$this->translator->trans('Blijf ingelogd')
			);
		}

		$builder->addFields($fields);

		$builder->setFormKnoppen(
			new TemplateFormKnoppen($this->twig, 'formulier/login_knoppen.html.twig')
		);
	}
}
