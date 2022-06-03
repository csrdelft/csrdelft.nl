<?php

namespace CsrDelft\view\forum;

use CsrDelft\entity\forum\ForumZoeken;
use CsrDelft\service\security\LoginService;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\HiddenField;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\CheckboxesField;
use CsrDelft\view\formulier\keuzevelden\DateField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\EmptyFormKnoppen;
use CsrDelft\view\formulier\knoppen\SubmitKnop;

class ForumZoekenForm extends Formulier
{

    /**
     * @param ForumZoeken $model
     */
    public function __construct($model)
    {
        parent::__construct($model, '/forum/zoeken');
        $this->showMelding = false;
        $this->css_classes[] = 'ForumZoekenForm';

        $fields = [];
        $fields['z'] = new TextField('zoekterm', $model->zoekterm, 'Zoekterm');
        $fields['z']->placeholder = 'Zoeken in forum';
        $fields['z']->enter_submit = true;

        if (LoginService::mag(P_LOGGED_IN)) {
            $fields[] = new SelectField('sorteer_volgorde', $model->sorteer_volgorde, 'Sorteervolgorde', [
                'desc' => 'Van hoog naar laag',
                'asc' => 'Van laag naar hoog'
            ]);
            $fields[] = new SelectField('sorteer_op', $model->sorteer_op, 'Sorteer op', [
                'aangemaakt_op' => 'Moment van aanmaken draad',
                'laatste_bericht' => 'Moment van plaatsen laatste bericht',
                'relevantie' => 'Relevantie'
            ]);
            $fields[] = new DateField('van', $model->van, 'Van', (int)date('Y'), 2006);
            $fields[] = new DateField('tot', $model->tot, 'Tot', (int)date('Y'), 2006);
            $fields[] = new CheckboxesField('zoek_in', $model->zoek_in, 'Zoek in', [
                'titel' => 'Titel',
                'alle_berichten' => 'Alle berichten',
                'eerste_bericht' => 'Alleen eerste Bericht',
            ]);
        }

        $fields[] = new HiddenField('limit', $model->limit);

        $this->addFields($fields);

        $this->formKnoppen = new EmptyFormKnoppen();
        $this->formKnoppen->css_classes[] = 'mb-3';
        $this->formKnoppen->addKnop(new SubmitKnop(null, 'submit', 'Zoeken'));
    }

}
