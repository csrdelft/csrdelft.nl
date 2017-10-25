<?php

namespace CsrDelft\view\formulier\invoervelden;

use CsrDelft\model\security\AccessModel;

/**
 * RechtenField.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 30/03/2017
 */
class RechtenField extends TextField {

	public function __construct($name, $value, $description) {
		parent::__construct($name, $value, $description);
		$this->suggestions[] = AccessModel::instance()->getPermissionSuggestions();

		// TODO: bundelen om simultane verbindingen te sparen
		foreach (array('verticalen', 'lichtingen', 'commissies', 'groepen', 'onderverenigingen', 'woonoorden') as $option) {
			$this->suggestions[ucfirst($option)] = '/groepen/' . $option . '/zoeken/?q=';
		}

		$this->title = 'Met , en + voor respectievelijk OR en AND. Gebruik | voor OR binnen AND (alsof er haakjes omheen staan)';
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		// parent checks not null
		if ($this->value == '') {
			return true;
		}
		$errors = AccessModel::instance()->getPermissionStringErrors($this->value);
		if (!empty($errors)) {
			$this->error = 'Ongeldig: "' . implode('" & "', $errors) . '"';
		}
		return $this->error === '';
	}

}
