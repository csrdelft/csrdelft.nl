<?php

namespace CsrDelft\view\eetplan;

use CsrDelft\model\groepen\WoonoordenModel;

class EetplanHuisView extends AbstractEetplanView {

	private $woonoord;

	public function __construct(
		$model,
		$lichting,
		$iHuisID
	) {
		parent::__construct($model, $lichting);
		$this->woonoord = WoonoordenModel::get($iHuisID);
	}

	public function getBreadcrumbs() {
		return parent::getBreadcrumbs() . ' » <a href="/groepen/woonoorden/' . $this->woonoord->id . '">' . $this->woonoord->naam . '</a>';
	}

	function view() {
		//feuten voor een huis tonen
		$this->smarty->assign('model', $this->model);
		$this->smarty->assign('eetplan', $this->model);
		$this->smarty->display('eetplan/huis.tpl');
	}
}
