<?php

namespace CsrDelft\view\groepen\formulier;

use CsrDelft\view\formulier\knoppen\FormKnoppen;
use CsrDelft\view\formulier\knoppen\PasfotoAanmeldenKnop;
use CsrDelft\view\formulier\knoppen\SubmitKnop;

class GroepAanmeldKnoppen extends FormKnoppen
{

    public $submit;

    public function __construct($pasfoto = false)
    {
        parent::__construct();
        if ($pasfoto) {
            $this->submit = new PasfotoAanmeldenKnop();
        } else {
            $this->submit = new SubmitKnop(null, 'submit', 'Aanmelden', null, null);
        }
        $this->addKnop($this->submit, true);
    }

}
