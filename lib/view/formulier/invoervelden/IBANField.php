<?php

namespace CsrDelft\view\formulier\invoervelden;

/**
 * @author Jorai Rijsdijk <jorairijsdijk@gmail.com>
 * @since 30/03/2017
 *
 * Class IBANField checked of de ingevulde bankrekening een valide IBAN is.
 */
class IBANField extends TextField
{

    public function validate()
    {
        if (!parent::validate()) {
            return false;
        }
        // parent checks not null
        if ($this->value == '') {
            return true;
        }
        // check format
        if (!verify_iban($this->value)) {
            $this->error = "Ongeldige IBAN";
        }
        return $this->error === '';
    }

}
