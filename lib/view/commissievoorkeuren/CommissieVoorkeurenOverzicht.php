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

	protected $categorieFormulier;
	protected $commissieFormulier;
	public function __construct($model = null, $commissieBoekFormulier = null, $categorieFormulier = null) {
		parent::__construct($model, "/commissievoorkeuren/");
		$this->categorieFormulier = $categorieFormulier ?? new AddCategorieFormulier(new VoorkeurCommissieCategorie());
		$this->commissieFormulier = $commissieBoekFormulier ?? new AddCommissieFormulier(new VoorkeurCommissie());

	}

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » <a href="/commissievoorkeuren">' . $this->getTitel() . '</a>';
	}

	public function getTitel() {
		return 'Voorkeuren voor commissies';
	}

	public function view() {
		$this->smarty->assign("categorieFormulier", $this->categorieFormulier);
		$this->smarty->assign("commissieFormulier", $this->commissieFormulier);
		$this->smarty->assign("categorien", $this->model);
		$this->smarty->display("commissievoorkeuren/overzicht.tpl");
	}

	public function getModel() {
		return null;
	}
}
