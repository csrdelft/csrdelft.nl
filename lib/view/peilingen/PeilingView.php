<?php

namespace CsrDelft\view\peilingen;

use CsrDelft\model\entity\peilingen\Peiling;
use CsrDelft\view\SmartyTemplateView;


class PeilingView extends SmartyTemplateView {

	/** @var bool */
	private $beheer;

	public function __construct(Peiling $peiling, $beheer = false) {
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
