<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\common\CsrGebruikerException;
use CsrDelft\entity\pin\PinTransactieMatch;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\CivisaldoField;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\JaNeeField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 24/02/2018
 */
class PinBestellingAanmakenForm extends ModalForm
{
    /**
     * @param PinTransactieMatch|null $pinTransactieMatch
     * @throws CsrGebruikerException
     */
    public function __construct($pinTransactieMatch = null)
    {
        parent::__construct([], '/fiscaat/pin/aanmaken', 'Voeg een bestelling toe', true);
        $comment = 'Pinbetaling ' . ($pinTransactieMatch ? PinTransactieMatch::renderMoment($pinTransactieMatch->getMoment(), false) : '');

        $fields = [];
        $fields[] = new HtmlComment('Er is geen bestelling gevonden voor deze transactie. Maak met onderstaand formulier een nieuwe bestelling aan met het aangegeven bedrag.');
        $fields['civisaldo'] = new CivisaldoField('uid', null, 'Account');
        $fields['civisaldo']->required = true;
        $fields['comment'] = new TextField('comment', $comment, 'Externe notitie');
        $fields['intern'] = new TextareaField('intern', $pinTransactieMatch ? $pinTransactieMatch->notitie : null, 'Interne notitie');
        $fields['stuurMail'] = new JaNeeField('stuurMail', true, 'Stuur mail naar lid');

        $fields['pinTransactieId'] = new RequiredIntField('pinTransactieId', $pinTransactieMatch ? $pinTransactieMatch->id : null, 'Pin Transactie Id');
        $fields['pinTransactieId']->hidden = true;

        $this->addFields($fields);

        $this->formKnoppen = new FormDefaultKnoppen();
    }
}
