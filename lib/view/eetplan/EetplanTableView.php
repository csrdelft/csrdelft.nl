<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\view\SmartyTemplateView;

/**
 * Class EetplanTableView Geef een tabel weer voor een eetplan
 *
 * Is gebasseerd op EetplanModel->getEetplan
 */
class EetplanTableView extends SmartyTemplateView {
	function view() {
		$this->smarty->assign('avonden', $this->model['avonden']);
		$this->smarty->assign('novieten', $this->model['novieten']);
		$this->smarty->display('eetplan/table.tpl');
	}
}
