<?php

namespace CsrDelft\view\maalcie\persoonlijk\voorkeuren;

use CsrDelft\model\entity\maalcie\CorveeVoorkeur;
use CsrDelft\view\SmartyTemplateView;

class MijnVoorkeurView extends SmartyTemplateView {
	private $ingeschakeld;

	public function __construct(CorveeVoorkeur $voorkeur, $ingeschakeld) {
		parent::__construct($voorkeur);
		$this->ingeschakeld = $ingeschakeld;
	}

	public function view() {
		$this->smarty->assign('ingeschakeld', $this->ingeschakeld);
		$this->smarty->assign('voorkeur', $this->model);
		$this->smarty->assign('uid', $this->model->uid);
		$this->smarty->assign('crid', $this->model->crv_repetitie_id);
		$this->smarty->display('maalcie/voorkeur/mijn_voorkeur_veld.tpl');
	}
}
