<?php

require_once 'model/PeilingenModel.class.php';

class PeilingView extends SmartyTemplateView {
	private $beheer;

	function PeilingView(Peiling $peiling, $beheer = false) {
		parent::__construct($peiling);
		$this->beheer = $beheer;
	}

    /**
     * Wordt gebruikt door de CsrBB parser
     *
     * @return string HTML van de peiling
     */
	public function getHtml() {
		$this->smarty->assign('peiling', $this->model);
		$this->smarty->assign('beheer', $this->beheer);
		return $this->smarty->fetch('peiling/peiling.bb.tpl');
	}

	public function view() {
	    echo $this->getHtml();
	}

}

class PeilingenBeheerView extends SmartyTemplateView {

	private $peiling;

	/**
	 * PeilingenBeheerView constructor.
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
		$this->smarty->assign("peilingen", $peilingen);
		$this->smarty->display('peiling/beheer.tpl');
	}

}
