<?php

namespace CsrDelft\view\formulier;

use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\knoppen\CancelKnop;

class FoutmeldingForm extends ModalForm
{
    /**
     * @param string $titel
     * @param string $melding
     */
    public function __construct($titel, $melding)
    {
        parent::__construct(null, '', $titel, true);
        $fields = [];
        $fields[] = new HtmlComment($melding);
        $this->addFields($fields);
        $this->formKnoppen = new CancelKnop();
    }
}
