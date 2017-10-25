<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\model\ProfielModel;

class EetplanNovietView extends AbstractEetplanView {

	private $uid;

	public function __construct(
		$model,
		$lichting,
		$uid
	) {
		parent::__construct($model, $lichting);
		$this->uid = $uid;
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » ' . ProfielModel::getLink($this->uid, 'civitas');
	}

	function view() {
		//huizen voor een feut tonen
		$this->smarty->assign('eetplan', $this->model);
		$this->smarty->display('eetplan/noviet.tpl');
	}

}
