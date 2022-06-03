<?php

namespace CsrDelft\view\maalcie\forms;

use CsrDelft\common\ContainerFacade;
use CsrDelft\entity\corvee\CorveeFunctie;
use CsrDelft\entity\corvee\CorveeRepetitie;
use CsrDelft\entity\corvee\CorveeTaak;
use CsrDelft\entity\maalcie\Maaltijd;
use CsrDelft\repository\corvee\CorveeFunctiesRepository;
use CsrDelft\view\formulier\getalvelden\required\RequiredIntField;
use CsrDelft\view\formulier\invoervelden\DoctrineEntityField;
use CsrDelft\view\formulier\invoervelden\LidObjectField;
use CsrDelft\view\formulier\keuzevelden\required\RequiredDateObjectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;
use CsrDelft\view\formulier\ModalForm;

/**
 * TaakForm.class.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor een nieuwe of te bewerken corveetaak.
 *
 */
class TaakForm extends ModalForm
{

    public function __construct(CorveeTaak $taak, $action)
    {
        parent::__construct($taak, '/corvee/beheer/' . $action);

        if ($taak->taak_id === null) {
            $this->titel = 'Corveetaak aanmaken';
        } else {
            $this->titel = 'Corveetaak wijzigen';
            $this->css_classes[] = 'PreventUnchanged';
        }

        $functieNamen = ContainerFacade::getContainer()->get(CorveeFunctiesRepository::class)->getAlleFuncties(); // grouped by functie_id
        $functiePunten = 'var punten=[];';
        foreach ($functieNamen as $functie) {
            $functieNamen[$functie->functie_id] = [
                'value' => $functie->naam,
                'label' => $functie->functie_id,
                'id' => $functie->functie_id,
            ];
            $functiePunten .= 'punten["' . $functie->naam . '"]=' . $functie->standaard_punten . ';';
            if ($taak->punten === null) {
                $taak->punten = $functie->standaard_punten;
            }
        }

        $fields = [];
        $fields['fid'] = new DoctrineEntityField('corveeFunctie', $taak->corveeFunctie, 'Functie', CorveeFunctie::class, '/corvee/functies/suggesties?q=');
        $fields['fid']->onchange = $functiePunten . "$('.punten_field').val(punten[this.value]);";
        $fields['fid']->required = true;
        $fields['lid'] = new LidObjectField('profiel', $taak->profiel, 'Naam');
        $fields['lid']->title = 'Bij het wijzigen van het toegewezen lid worden ook de corveepunten aan het nieuwe lid gegeven.';
        $fields[] = new RequiredDateObjectField('datum', $taak->datum, 'Datum', date('Y') + 2, date('Y') - 2);
        $fields['ptn'] = new RequiredIntField('punten', $taak->punten, 'Punten', 0, 10);
        $fields['ptn']->css_classes[] = 'punten_field';
        $fields[] = new RequiredIntField('bonus_malus', $taak->bonus_malus, 'Bonus/malus', -10, 10);
        $fields['crid'] = new DoctrineEntityField('corveeRepetitie', $taak->corveeRepetitie, '', CorveeRepetitie::class, '');
        $fields['crid']->readonly = true;
        $fields['crid']->hidden = true;
        $fields['mid'] = new DoctrineEntityField('maaltijd', $taak->maaltijd, 'Gekoppelde maaltijd', Maaltijd::class, '/maaltijden/beheer/suggesties?q=');
        $fields['mid']->title = 'De maaltijd waar deze taak bij hoort.';

        $this->addFields($fields);

        $this->formKnoppen = new FormDefaultKnoppen();
    }

}
