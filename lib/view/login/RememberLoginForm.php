<?php

namespace CsrDelft\view\login;

use CsrDelft\entity\security\RememberLogin;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\required\RequiredTextField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

class RememberLoginForm extends ModalForm
{

    public function __construct(RememberLogin $remember)
    {
        parent::__construct($remember, '/session/remember', 'Automatisch inloggen vanaf huidig apparaat', true);

        $fields = [];
        $fields[] = new HiddenField('DataTableSelection', $remember->id ? $remember->id . "@rememberlogin.csrdelft.nl" : null);
        $fields[] = new HtmlComment('<div class="dikgedrukt">Gebruik deze functie alleen voor een veilig apparaat op een veilige locatie.</div>');
        $fields[] = new RequiredTextField('device_name', $remember->device_name, 'Naam apparaat');

        $this->addFields($fields);

        $this->formKnoppen = new FormDefaultKnoppen('/', false, true, true, true, false, true);
    }

}
