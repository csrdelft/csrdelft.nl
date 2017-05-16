<?php
/**
 * The ${NAME} file.
 */

namespace CsrDelft\view\commissievoorkeuren;

use CsrDelft\model\CommissieVoorkeurenModel;
use CsrDelft\model\entity\Profiel;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\TextareaField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\FormDefaultKnoppen;

class CommissieVoorkeurenForm extends Formulier
{

    public function getBreadcrumbs()
    {
        return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » ' . $this->model->getLink('civitas') . ' » <span class="active">' . $this->titel . '</span>';
    }

    public function __construct(Profiel $profiel)
    {
        parent::__construct($profiel, '/profiel/' . $profiel->uid . '/voorkeuren', 'Commissie-voorkeuren');

        //permissies
        $opties = array(1 => 'nee', 2 => 'misschien', 3 => 'ja');

        $model = new CommissieVoorkeurenModel($profiel->uid);
        $commissies = $model->getCommissies();
        $voorkeuren = $model->getVoorkeur();

        $fields[] = new HtmlComment('<p>Hier kunt u per commissie opgeven of u daar interesse in heeft!</p>');
        foreach ($commissies as $id => $comm) {
            $fields[] = new SelectField('comm' . $id, $this->getVoorkeur($voorkeuren, $id), $comm, $opties);
        }
        $fields[] = new TextareaField('lidOpmerking', $model->getLidOpmerking(), 'Vul hier je eventuele voorkeur voor functie in, of andere opmerkingen');
        $fields[] = new FormDefaultKnoppen('/profiel/' . $profiel->uid);

        $this->addFields($fields);
    }

    private function getVoorkeur(
        $voorkeur,
        $id
    ) {
        if (array_key_exists($id, $voorkeur)) {
            return $voorkeur[$id];
        }
        return 0;
    }

}
