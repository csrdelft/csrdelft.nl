<?php

namespace CsrDelft\view\bibliotheek;

use CsrDelft\model\bibliotheek\BiebBoek;

/**
 * Boek weergeven
 */
class BibliotheekBoekContent extends AbstractBibliotheekView {

	public function __construct(BiebBoek $boek) {
		parent::__construct($boek);
	}

	public function getTitel() {
		return 'Bibliotheek - Boek: ' . $this->model->getTitel();
	}

	public function view() {
		$this->smarty->assign('boek', $this->model);
		$this->smarty->display('bibliotheek/boek.tpl');
	}

}
