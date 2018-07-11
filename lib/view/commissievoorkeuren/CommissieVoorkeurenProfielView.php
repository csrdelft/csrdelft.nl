<?php

namespace CsrDelft\view\commissievoorkeuren;

use CsrDelft\controller\CommissieVoorkeurenController;
use CsrDelft\model\commissievoorkeuren\CommissieVoorkeurenModel;
use CsrDelft\model\commissievoorkeuren\CommissieVoorkeurModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurCommissieModel;
use CsrDelft\model\commissievoorkeuren\VoorkeurOpmerkingModel;
use CsrDelft\model\entity\profiel\Profiel;
use CsrDelft\model\ProfielModel;
use CsrDelft\view\SmartyTemplateView;
use CsrDelft\view\View;

class CommissieVoorkeurenProfielView extends SmartyTemplateView {
	public function __construct($profiel) {
		parent::__construct($profiel);
	}

	public function getModel() {
		return $this->profiel;
	}

	public function getBreadcrumbs() {
		return '<a href="/ledenlijst" title="Ledenlijst"><span class="fa fa-user module-icon"></span></a> » <a href="/commissievoorkeuren">Voorkeuren voor commissies</a> » <span class="active">' . $this->getTitel() . '</span>';
	}

	public function getTitel() {
		return 'Voorkeur van lid';
	}

	public function view() {
		$voorkeuren = CommissieVoorkeurModel::instance()->getVoorkeurenVoorLid($this->model);
		$voorkeurenMap = array();
		foreach ($voorkeuren as $voorkeur) {
			$voorkeurenMap[$voorkeur->cid] = $voorkeur;
		}
		$commissies = VoorkeurCommissieModel::instance()->find('zichtbaar = 1');
		$opmerking = VoorkeurOpmerkingModel::instance()->getOpmerkingVoorLid($this->model);

		$this->smarty->assign('profiel', $this->model);
		$this->smarty->assign('voorkeuren', $voorkeurenMap);
		$this->smarty->assign('commissies', $commissies);
		$this->smarty->assign('lidOpmerking', $opmerking->lidOpmerking);
		$this->smarty->assign('opmerkingForm', new CommissieVoorkeurPraesesOpmerkingForm($opmerking));
		$this->smarty->display('commissievoorkeuren/profiel.tpl');


	}

}
