<?php

namespace CsrDelft\view\profiel;

use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\getalvelden\TelefoonField;
use CsrDelft\view\formulier\invoervelden\EmailField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\knoppen\SubmitKnop;

class InschrijfLinkForm extends Formulier
{

	public function __construct()
	{
		parent::__construct(null, '/inschrijflink');

		$fields = [];
		$fields['voornaam'] = new RequiredTextField('voornaam', '', 'Voornaam');
		$fields['voornaam']->autocomplete = false;
		$fields['tussenvoegsel'] = new TextField('tussenvoegsel', '', 'Tussenvoegsel');
		$fields['tussenvoegsel']->autocomplete = false;
		$fields['achternaam'] = new TextField('achternaam', '', 'Achternaam');
		$fields['achternaam']->autocomplete = false;
		$fields['email'] = new EmailField('email', '', 'E-mail');
		$fields['email']->autocomplete = false;
		$fields['mobiel'] = new TelefoonField('mobiel', '', 'Mobiel');
		$fields['mobiel']->autocomplete = false;

		$fields[] = new SubmitKnop(null, 'submit', 'Link genereren', 'Link genereren', false);

		$this->addFields($fields);
	}

}
