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
use CsrDelft\view\formulier\keuzevelden\CheckboxField;
use CsrDelft\view\formulier\keuzevelden\SelectField;
use CsrDelft\view\formulier\knoppen\SubmitKnop;
use CsrDelft\view\View;

class CommissieVoorkeurenView extends Formulier {


	public function __construct($model) {
		parent::__construct($model, null);
        $opties = [];
        foreach (VoorkeurCommissieCategorieModel::instance()->find() as $cat) {
            $opties[$cat->id] = $cat->naam;
        }
        $this->addFields([new HtmlComment("<p>Hier kunnen instellingen voor de commissie worden aangepast. Onderaan de pagina staan de leden die een voorkeur voor deze commissie hebben opgegeven.</p>")]);
        $this->addFields([new CheckboxField('zichtbaar', $this->model->zichtbaar, "Tonen aan leden")]);
        $this->addFields([new SelectField("categorie_id", $this->model->categorie_id, "Categorie", $opties)]);
        $this->addFields([new SubmitKnop()]);

	}

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » <a href="/commissievoorkeuren">Commissievoorkeuren</a>»<span>'.$this->getTitel() .'</span>';
	}

	public function getTitel() {
		return $this->model->naam;
	}
    public function view() {
	    parent::view();
        $format = array('', 'nee', 'misschien', 'ja');
        $commissie = $this->model;
        echo '<table><tr><td><h4>Lid</h4></td><td><h4>Interesse</h4></td></tr>';
        $voorkeuren = CommissieVoorkeurModel::instance()->getVoorkeurenVoorCommissie($commissie, 2);
        foreach ($voorkeuren as $voorkeur) {
            echo '<tr ' . ($voorkeur->heeftGedaan() ? 'style="opacity: .50"' : '') . '><td><a href="/commissievoorkeuren/lidpagina/' . $voorkeur->uid . '">' . $voorkeur->getProfiel()->getNaam() . '</a></td><td>' . $format[$voorkeur->voorkeur] . '</td></tr>';
        }
        echo '</table>';


    }


}
