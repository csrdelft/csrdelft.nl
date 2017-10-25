<?php

namespace CsrDelft\view\instellingen;

use CsrDelft\model\entity\Instelling;
use CsrDelft\view\SmartyTemplateView;

class InstellingBeheerView extends SmartyTemplateView {

	public function __construct(Instelling $instelling) {
		parent::__construct($instelling);
	}

	public function view() {
		$this->smarty->assign('module', $this->model->module);
		$this->smarty->assign('id', $this->model->instelling_id);
		$this->smarty->assign('waarde', $this->model->waarde);
		$this->smarty->display('instellingen/beheer/instelling_row.tpl');
	}

}
