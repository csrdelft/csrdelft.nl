<?php

namespace CsrDelft\view\login;

use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\WachtwoordField;
use CsrDelft\view\formulier\knoppen\CancelKnop;
use CsrDelft\view\formulier\knoppen\SubmitKnop;

/**
 * Om een nieuwe login token te krijgen.
 *
 * @package CsrDelft\view\login
 */
class UpdateLoginForm extends Formulier
{
	public function __construct($action)
	{
		parent::__construct(null, $action, 'Opnieuw inloggen');

		$this->css_classes[] = 'modal-dialog modal-content modal-body';

		$fields = [];
		$fields['pass'] = new WachtwoordField('pass', null, null);
		$fields['pass']->placeholder = 'Wachtwoord';

		$this->addFields($fields);

		$this->formKnoppen->addKnop(
			new SubmitKnop(null, 'submit', 'Inloggen', 'Inloggen', 'key')
		);
		$this->formKnoppen->addKnop(new CancelKnop());
	}
}
