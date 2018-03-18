<?php

namespace CsrDelft\view\commissievoorkeuren;

use CsrDelft\model\commissievoorkeuren\CommissieVoorkeurModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieCategorieModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieModel;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurCommissie;
use CsrDelft\model\entity\commissievoorkeuren\VoorkeurCommissieCategorie;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\formulier\elementen\HtmlComment;
use CsrDelft\view\formulier\Formulier;
use CsrDelft\view\formulier\invoervelden\TextField;
use CsrDelft\view\formulier\keuzevelden\CheckboxField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\SubmitKnop;
use CsrDelft\view\View;

class CommissieVoorkeurenOverzicht extends Formulier {


	public function __construct($model = null) {
		parent::__construct($model, "/commissievoorkeuren/");
        $this->addFields([new HtmlComment($this->buildPage())]);
        $this->addFields([new HtmlComment("<h2>Categorie toevoegen</h2>")]);
        $this->addFields([new TextField("categorienaam", "", "Naam nieuwe categorie")]);
        $this->addFields([new HtmlComment("<input type=submit name=nieuwecategorie value='Categorie toevoegen' />")]);
        $this->addFields([new HtmlComment("<h2>Commissie toevoegen</h2>")]);
        $this->addFields([new TextField("commissienaam", "", "Naam nieuwe commissie")]);
        $this->addFields([new HtmlComment("<input type=submit name=nieuwecommissie value='Commissie toevoegen' />")]);
	}

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » <a href="/commissievoorkeuren">' . $this->getTitel() . '</a>';
	}

	public function getTitel() {
		return 'Voorkeuren voor commissies';
	}
    public function buildPage() {
	    $output = '<p>klik op een commissie om de voorkeuren te bekijken';

        $cat2commissie = VoorkeurCommissieModel::instance()->getByCategorie();
        foreach ($cat2commissie as $id=> $categorie) {
            $output .= '<h2>' . $categorie["categorie"]->naam . ' </h2>';
            $output .= '<ul>';
            foreach ($categorie["commissies"] as $commissie) {
                $output .= '<li ' . (!$commissie->zichtbaar ? 'style="opacity: .50"' : '') . ' > <a href="/commissievoorkeuren/overzicht/' . $commissie->id . '" >' . $commissie->naam . '</a></li>';
            }
            $output .= '</ul>';
        }
        return $output;

    }


}
