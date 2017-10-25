<?php

namespace CsrDelft\view\maalcie\corvee\voorkeuren;

use CsrDelft\model\entity\maalcie\CorveeVoorkeur;
use CsrDelft\view\SmartyTemplateView;

class BeheerVoorkeurView extends SmartyTemplateView {

	public function __construct(CorveeVoorkeur $voorkeur) {
		parent::__construct($voorkeur);
	}

	public function view() {
		$this->smarty->assign('voorkeur', $this->model);
		$this->smarty->assign('crid', $this->model->crv_repetitie_id);
		$this->smarty->assign('uid', $this->model->uid);
		$this->smarty->display('maalcie/voorkeur/beheer_voorkeur_veld.tpl');
	}

}
