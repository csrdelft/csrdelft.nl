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
use CsrDelft\view\SmartyTemplateView;
use CsrDelft\view\View;

class CommissieVoorkeurenOverzicht extends SmartyTemplateView {


	public function __construct($model = null) {
		parent::__construct($model, "/commissievoorkeuren/");
	}

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » <a href="/commissievoorkeuren">' . $this->getTitel() . '</a>';
	}

	public function getTitel() {
		return 'Voorkeuren voor commissies';
	}

	public function view() {


		$this->smarty->assign("categorieFormulier", new AddCategorieFormulier(new VoorkeurCommissieCategorie()));
		$this->smarty->assign("commissieFormulier", new AddCommissieFormulier(new VoorkeurCommissie()));
		$this->smarty->assign("categorien", VoorkeurCommissieModel::instance()->getByCategorie());
		$this->smarty->display("commissievoorkeuren/overzicht.tpl");
	}

	public function getModel() {
		return null;
	}
}
