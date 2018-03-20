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
use CsrDelft\view\SmartyTemplateView;
use CsrDelft\view\View;

class CommissieVoorkeurenView extends SmartyTemplateView {


	public function __construct($model) {
		parent::__construct($model);
	}

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » <a href="/commissievoorkeuren">Commissievoorkeuren</a>»<span>' . $this->getTitel() . '</span>';
	}

	public function getTitel() {
		return $this->model->naam;
	}

	public function view() {
		$this->smarty->assign('voorkeuren', CommissieVoorkeurModel::instance()->getVoorkeurenVoorCommissie($this->model, 2));
		$this->smarty->assign('commissie', $this->model);
		$this->smarty->assign('commissieFormulier', new CommissieFormulier($this->model));
		$this->smarty->display('commissievoorkeuren/commissie.tpl');

	}


}
