<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\model\entity\security\Account;
use CsrDelft\model\security\AccountModel;

/**
 * WachtwoordWijzigenField.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 *
 * WachtwoordWijzigenField
 *
 * Aanpassen van wachtwoorden.
 * Vreemde eend in de 'bijt', deze unit produceert 3 velden: oud, nieuw en bevestiging.
 *
 * Bij wachtwoord resetten produceert deze 2 velden.
 */
class WachtwoordWijzigenField extends InputField {

	private $require_current;

	public function __construct($name, Account $account, $require_current = true) {
		$this->require_current = $require_current;
		parent::__construct($name, null, null, $account);
		$this->title = 'Het nieuwe wachtwoord moet langer zijn dan 23 tekens of langer dan 10 en ook hoofdletters, kleine letters, cijfers en speciale tekens bevatten.';

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
		$profiel = $account->getProfiel();
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
		$this->blacklist = array_filter_empty($this->blacklist);

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

	public function isPosted() {
		if ($this->require_current AND !isset($_POST[$this->name . '_current'])) {
			return false;
		}
		return isset($_POST[$this->name . '_new']) AND isset($_POST[$this->name . '_confirm']);
	}

	public function getValue() {
		if ($this->isPosted()) {
			$this->value = $_POST[$this->name . '_new'];
		} else {
			$this->value = false;
		}
		if ($this->empty_null AND $this->value == '') {
			return null;
		}
		return $this->value;
	}

	public function checkZwarteLijst($pass_plain) {
		foreach ($this->blacklist as $disallowed) {
			if (stripos($pass_plain, $disallowed) !== false) {
				$this->error = htmlspecialchars($disallowed);
				return true;
			}
		}
		return false;
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->require_current) {
			$current = $_POST[$this->name . '_current'];
		}
		// filter_input does not use current value in $_POST
		$new = $_POST[$this->name . '_new'];
		$confirm = $_POST[$this->name . '_confirm'];
		$length = strlen(utf8_decode($new));
		if ($this->require_current AND empty($current)) {
			$this->error = 'U moet uw huidige wachtwoord invoeren';
		} elseif ($this->required AND empty($new)) {
			$this->error = 'U moet een nieuw wachtwoord invoeren';
		} elseif (!empty($new)) {
			if ($this->require_current AND $current == $new) {
				$this->error = 'Het nieuwe wachtwoord is hetzelfde als het huidige wachtwoord';
			} elseif ($length < 10) {
				$this->error = 'Het nieuwe wachtwoord moet minimaal 10 tekens lang zijn';
			} elseif ($length > 100) {
				$this->error = 'Het nieuwe wachtwoord mag maximaal 100 tekens lang zijn';
			} elseif ($this->checkZwarteLijst($new)) {
				$this->error = 'Het nieuwe wachtwoord of een deel ervan staat op de zwarte lijst: "' . $this->error . '"';
			} elseif (preg_match('/^[0-9]*$/', $new)) {
				$this->error = 'Het nieuwe wachtwoord mag niet uit alleen getallen bestaan';
			} elseif ($length < 23) {
				if (preg_match('/^[a-zA-Z]*$/', $new)) {
					$this->error = 'Het nieuwe wachtwoord moet ook cijfers en speciale tekens bevatten<br />of langer zijn dan 23 tekens';
				} elseif (preg_match('/^[0-9a-z]*$/', $new)) {
					$this->error = 'Het nieuwe wachtwoord moet ook hoofdletters en speciale tekens bevatten<br />of langer zijn dan 23 tekens';
				} elseif (preg_match('/^[0-9A-Z]*$/', $new)) {
					$this->error = 'Het nieuwe wachtwoord moet ook kleine letters en speciale tekens bevatten<br />of langer zijn dan 23 tekens';
				} elseif (preg_match('/^[0-9a-zA-Z]*$/', $new)) {
					$this->error = 'Het nieuwe wachtwoord moet ook speciale tekens bevatten<br />of langer zijn dan 23 tekens';
				}
			} elseif (preg_match('/(.)\1\1+/', $new) OR preg_match('/(.{3,})\1+/', $new) OR preg_match('/(.{4,}).*\1+/', $new)) {
				$this->error = 'Het nieuwe wachtwoord bevat teveel herhaling';
			} elseif (empty($confirm)) {
				$this->error = 'Vul uw nieuwe wachtwoord twee keer in';
			} elseif ($new != $confirm) {
				$this->error = 'Nieuwe wachtwoorden komen niet overeen';
			} elseif ($this->require_current AND !AccountModel::instance()->controleerWachtwoord($this->model, $current)) {
				$this->error = 'Uw huidige wachtwoord is niet juist';
			}
		}
		return $this->error === '';
	}

	public function getHtml() {
		$html = '';
		if ($this->require_current) {
			$html .= '<div class="WachtwoordField"><label for="' . $this->getId() . '_current">Huidig wachtwoord' . ($this->require_current ? '<span class="required"> *</span>' : '') . '</label>';
			$html .= '<input type="password" autocomplete="off" id="' . $this->getId() . '_current" name="' . $this->name . '_current" /></div>';
		}
		$html .= '<div class="WachtwoordField"><label for="' . $this->getId() . '_new">Nieuw wachtwoord' . ($this->required ? '<span class="required"> *</span>' : '') . '</label>';
		$html .= '<input type="password" autocomplete="off" id="' . $this->getId() . '_new" name="' . $this->name . '_new" /></div>';
		$html .= '<div class="WachtwoordField"><label for="' . $this->getId() . '_confirm">Herhaal nieuw wachtwoord' . ($this->required ? '<span class="required"> *</span>' : '') . '</label>';
		$html .= '<input type="password" autocomplete="off" id="' . $this->getId() . '_confirm" name="' . $this->name . '_confirm" /></div>';
		return $html;
	}

}
