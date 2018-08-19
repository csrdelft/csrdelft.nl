<?php

namespace CsrDelft\view\login;

use CsrDelft\model\entity\security\RememberLogin;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\invoervelden\RequiredTextField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class RememberLoginForm extends ModalForm {

	public function __construct(RememberLogin $remember) {
		parent::__construct($remember, '/loginremember', 'Automatisch inloggen vanaf huidig apparaat', true);

		$fields = [];
		$fields[] = new HtmlComment('<div class="dikgedrukt">Gebruik deze functie alleen voor een veilig apparaat op een veilige locatie.</div>');
		$fields[] = new RequiredTextField('device_name', $remember->device_name, 'Naam apparaat');

		$this->addFields($fields);

		$this->formKnoppen = new FormDefaultKnoppen('/', false, true, true, true, false, true);
	}

}
