<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\model\bibliotheek\BoekModel;
use CsrDelft\model\entity\bibliotheek\Boek;
use CsrDelft\view\SmartyTemplateView;
use CsrDelft\view\View;

/**
 * Boek weergeven
 */
class BibliotheekBoekContent extends SmartyTemplateView {

	public $formulier;
	public function __construct(Boek $boek, BoekFormulier $formulier) {
		parent::__construct($boek);
		$this->formulier = $formulier;

	}

	public function getTitel() {
		return 'Bibliotheek - Boek: ' . $this->model->getTitel();
	}

	public function view() {
		$this->smarty->assign('boek', $this->model);
		$this->smarty->assign('formulier', $this->formulier);
		$this->smarty->display('bibliotheek/boek.tpl');
	}


}
