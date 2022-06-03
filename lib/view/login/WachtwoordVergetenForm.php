<?php

namespace CsrDelft\view\login;

use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\required\RequiredEmailField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

class WachtwoordVergetenForm extends Formulier
{

    public function __construct()
    {
        parent::__construct(null, '/wachtwoord/vergeten', 'Wachtwoord vergeten');

        $fields = [];
        $fields[] = new RequiredEmailField('mail', null, 'E-mailadres');
        $fields[] = new FormDefaultKnoppen('/', false, true, true, true);

        $this->addFields($fields);
    }

}
