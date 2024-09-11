<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\common\ContainerFacade;
use CsrDelft\common\Util\ArrayUtil;
use CsrDelft\entity\security\Account;
use CsrDelft\service\AccountService;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 *
 * WachtwoordWijzigenField
 *
 * Aanpassen van wachtwoorden.
 * Vreemde eend in de 'bijt', deze unit produceert 3 velden: oud, nieuw en bevestiging.
 *
 * Bij wachtwoord resetten produceert deze 2 velden.
 */
class WachtwoordWijzigenField extends InputField
{
	protected $fieldClassName = '';
	protected $wrapperClassName = '';

	public function __construct(
		$name,
		Account $account,
		private $require_current = true
	) {
		parent::__construct($name, null, '', $account);
		$this->title =
			'Het nieuwe wachtwoord moet langer zijn dan 23 tekens of langer dan 10 en ook hoofdletters, kleine letters, cijfers en speciale tekens bevatten.';

		// blacklist gegevens van account
		$this->blacklist[] = $account->username;
		foreach (explode('@', $account->email) as $email) {
			foreach (explode('.', $email) as $part) {
				if (strlen($part) >= 5) {
					$this->blacklist[] = $part;
				}
			}
		}

		// blacklist gegevens van profiel
		$profiel = $account->profiel;
		$this->blacklist[] = $profiel->uid;
		$this->blacklist[] = $profiel->voornaam;
		foreach (explode(' ', $profiel->achternaam) as $part) {
			if (strlen($part) >= 4) {
				$this->blacklist[] = $part;
			}
		}
		$this->blacklist[] = $profiel->postcode;
		$this->blacklist[] = str_replace(' ', '', $profiel->postcode);
		$this->blacklist[] = $profiel->telefoon;
		$this->blacklist[] = $profiel->mobiel;

		// wis lege waarden
		$this->blacklist = ArrayUtil::array_filter_empty($this->blacklist);

		// algemene blacklist
		$this->blacklist[] = '1234';
		$this->blacklist[] = 'abcd';
		$this->blacklist[] = 'qwerty';
		$this->blacklist[] = 'azerty';
		$this->blacklist[] = 'asdf';
		$this->blacklist[] = 'jkl;';
		$this->blacklist[] = 'password';
		$this->blacklist[] = 'wachtwoord';
	}

	public function isPosted()
	{
		if ($this->require_current && !isset($_POST[$this->name . '_current'])) {
			return false;
		}
		return isset($_POST[$this->name . '_new']) &&
			isset($_POST[$this->name . '_confirm']);
	}

	public function getValue()
	{
		if ($this->isPosted()) {
			$this->value = $_POST[$this->name . '_new'];
		} else {
			$this->value = false;
		}
		if ($this->empty_null && $this->value == '') {
			return null;
		}
		return $this->value;
	}

	public function checkZwarteLijst($pass_plain)
	{
		foreach ($this->blacklist as $disallowed) {
			if (stripos((string) $pass_plain, (string) $disallowed) !== false) {
				$this->error = htmlspecialchars((string) $disallowed);
				return true;
			}
		}
		return false;
	}

	public function validate()
	{
		$accountService = ContainerFacade::getContainer()->get(
			AccountService::class
		);
		if (!parent::validate()) {
			return false;
		}
		if ($this->require_current) {
			$current = $_POST[$this->name . '_current'];
		}
		// filter_input does not use current value in $_POST
		$new = $_POST[$this->name . '_new'];
		$confirm = $_POST[$this->name . '_confirm'];
		$length = strlen(mb_convert_encoding($new, 'ISO-8859-1'));
		if ($this->require_current and empty($current)) {
			$this->error = 'U moet uw huidige wachtwoord invoeren';
		} elseif ($this->required and empty($new)) {
			$this->error = 'U moet een nieuw wachtwoord invoeren';
		} elseif (
			$this->require_current and
			!$accountService->controleerWachtwoord($this->model, $current)
		) {
			$this->error = 'Uw huidige wachtwoord is niet juist';
		} elseif (!empty($new)) {
			if ($this->require_current and $current == $new) {
				$this->error =
					'Het nieuwe wachtwoord is hetzelfde als het huidige wachtwoord';
			} elseif ($length < 10) {
				$this->error =
					'Het nieuwe wachtwoord moet minimaal 10 tekens lang zijn';
			} elseif ($length > 100) {
				$this->error =
					'Het nieuwe wachtwoord mag maximaal 100 tekens lang zijn';
			} elseif ($this->checkZwarteLijst($new)) {
				$this->error =
					'Het nieuwe wachtwoord of een deel ervan staat op de zwarte lijst: "' .
					$this->error .
					'"';
			} elseif (preg_match('/^[0-9]*$/', (string) $new)) {
				$this->error =
					'Het nieuwe wachtwoord mag niet uit alleen getallen bestaan';
			} elseif ($length < 23) {
				if (preg_match('/^[a-zA-Z]*$/', (string) $new)) {
					$this->error =
						'Het nieuwe wachtwoord moet ook cijfers en speciale tekens bevatten<br />of langer zijn dan 23 tekens';
				} elseif (preg_match('/^[0-9a-z]*$/', (string) $new)) {
					$this->error =
						'Het nieuwe wachtwoord moet ook hoofdletters en speciale tekens bevatten<br />of langer zijn dan 23 tekens';
				} elseif (preg_match('/^[0-9A-Z]*$/', (string) $new)) {
					$this->error =
						'Het nieuwe wachtwoord moet ook kleine letters en speciale tekens bevatten<br />of langer zijn dan 23 tekens';
				} elseif (preg_match('/^[0-9a-zA-Z]*$/', (string) $new)) {
					$this->error =
						'Het nieuwe wachtwoord moet ook speciale tekens bevatten<br />of langer zijn dan 23 tekens';
				}
			}

			if (
				preg_match('/(.)\1\1+/', (string) $new) ||
				preg_match('/(.{3,})\1+/', (string) $new) ||
				preg_match('/(.{4,}).*\1+/', (string) $new)
			) {
				$this->error = 'Het nieuwe wachtwoord bevat teveel herhaling';
			} elseif (empty($confirm)) {
				$this->error = 'Vul uw nieuwe wachtwoord twee keer in';
			} elseif ($new != $confirm) {
				$this->error = 'Nieuwe wachtwoorden komen niet overeen';
			}
		}
		return $this->error === '';
	}

	public function getHtml()
	{
		$html = '';
		if ($this->error !== '') {
			$this->css_classes[] = 'is-invalid';
		}
		$inputCssClasses = join(' ', $this->css_classes);

		if ($this->require_current) {
			$html .= <<<HTML
<div class="mb-3 row">
	<div class="{$this->labelClassName}">
		<label for="{$this->getId()}_current">Huidig wachtwoord<span class="required">*</span></label>
	</div>
	<div class="col-9">
		<input type="password" class="$inputCssClasses" autocomplete="off" id="{$this->getId()}_current" name="{$this->name}_current" />
	</div>
</div>
HTML;
		}

		$required = $this->required ? '<span class="required"> *</span>' : '';
		$html .= <<<HTML
<div class="mb-3 row">
	<div class="{$this->labelClassName}">
		<label for="{$this->getId()}_new">Nieuw wachtwoord{$required}</label>
	</div>
	<div class="col-9">
		<input type="password" class="$inputCssClasses" autocomplete="off" id="{$this->getId()}_new" name="{$this->name}_new" />
	</div>
</div>
<div class="mb-3 row">
	<div class="{$this->labelClassName}">
		<label for="{$this->getId()}_confirm">Herhaal nieuw wachtwoord{$required}</label>
	</div>
	<div class="col-9">
		<input type="password" class="$inputCssClasses" autocomplete="off" id="{$this->getId()}_confirm" name="{$this->name}_confirm" />
	</div>
</div>
HTML;
		return $html;
	}

	public function getErrorDiv()
	{
		if ($this->getError() != '') {
			return '<div class="d-block invalid-feedback">' .
				$this->getError() .
				'</div>';
		}
		return '';
	}
}
