<?php

namespace CsrDelft\view\login;

use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\RequiredTextField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

class VerifyForm extends Formulier {

	public function __construct($tokenString) {
		parent::__construct(null, '/verify/' . $tokenString, 'Verifieren');

		$fields[] = new RequiredTextField('user', null, 'Lidnummer');
		$fields[] = new FormDefaultKnoppen('/', false, true, true, true);

		$this->addFields($fields);
	}

}
