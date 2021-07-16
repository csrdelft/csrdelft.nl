<?php

namespace CsrDelft\view\formulier\invoervelden;

/**
 * @author P.W.G. Brussee <brussee@live.nl>
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 30/03/2017
 */
class FileNameField extends TextField {

	/**
	 * Trailing whitespace kan voor problemen zorgen bij het aanmaken van fotoalbums.
	 *
	 * @return string
	 */
	public function getValue() {
		return trim(parent::getValue());
	}

	public function validate() {
		if (!parent::validate()) {
			return false;
		}
		if ($this->value !== '' AND !valid_filename($this->value)) {
			$this->error = 'Ongeldige bestandsnaam';
		}
		return $this->error === '';
	}

}
