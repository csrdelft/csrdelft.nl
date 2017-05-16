<?php

namespace CsrDelft\view\login;

use CsrDelft\model\entity\security\Account;
use CsrDelft\view\formulier\elementen\HtmlBbComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\RequiredEmailField;
use CsrDelft\view\formulier\invoervelden\RequiredWachtwoordWijzigenField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

class WachtwoordWijzigenForm extends Formulier
{

    public function __construct(
        Account $account,
        $action,
        $require_current = true
    ) {
        parent::__construct($account, '/wachtwoord/' . $action, 'Wachtwoord instellen');

        if ($account->email == '') {
            setMelding('Vul uw e-mailadres in om uw wachtwoord te kunnen resetten als u deze bent vergeten.', 0);
            $fields[] = new RequiredEmailField('email', $account->email, 'E-mailadres');
        }
        $fields[] = new RequiredWachtwoordWijzigenField('wijzigww', $account, $require_current);
        $fields[] = new FormDefaultKnoppen('/', false, true, true, true);
        $fields[] = new HtmlBbComment('[div h=50][/div][h=5]Wat is een goed wachtwoord?[/h][video]www.youtube.com/watch?v=0SkdP36wiAU[/video]');

        $this->addFields($fields);
    }

}
