<?php

namespace CsrDelft\view\maalcie\persoonlijk\voorkeuren;

use CsrDelft\model\entity\maalcie\CorveeVoorkeur;
use CsrDelft\view\SmartyTemplateView;

class MijnVoorkeurView extends SmartyTemplateView {
	public function __construct(CorveeVoorkeur $voorkeur) {
		parent::__construct($voorkeur);
	}

	public function view() {
		$this->smarty->assign('voorkeur', $this->model);
		$this->smarty->assign('uid', $this->model->uid);
		$this->smarty->assign('crid', $this->model->crv_repetitie_id);
		$this->smarty->display('maalcie/voorkeur/mijn_voorkeur_veld.tpl');
	}
}
