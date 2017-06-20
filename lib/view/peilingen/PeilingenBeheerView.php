<?php

namespace CsrDelft\view\peilingen;

use CsrDelft\model\entity\peilingen\Peiling;
use CsrDelft\view\SmartyTemplateView;

class PeilingenBeheerView extends SmartyTemplateView {

	private $peiling;

	/**
	 * PeilingenBeheerView constructor.
	 *
	 * @param $model Peiling[]
	 */
	public function __construct($model, $peiling) {
		parent::__construct($model);
		$this->peiling = $peiling;
	}

	public function getModel() {
		return $this->peiling;
	}

	public function getBreadcrumbs() {
		return null;
	}

	public function getTitel() {
		return 'Peilingbeheer';
	}

	public function view() {
		$peilingen = array();
		foreach ($this->model as $peiling) {
			$peilingen[] = new PeilingView($peiling, true);
		}
		$this->smarty->assign("peiling", $this->peiling);
		$this->smarty->assign("peilingen", $peilingen);
		$this->smarty->display('peiling/beheer.tpl');
	}

}
