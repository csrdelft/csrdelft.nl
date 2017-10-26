<?php

namespace CsrDelft\view\fiscaat\saldo;


use CsrDelft\view\SmartyTemplateView;
use DateTime;

class SaldiSommenResponseView extends SmartyTemplateView {

	private $moment;

	public function __construct($model, DateTime $moment, $titel = false) {
		parent::__construct($model, $titel);

		$this->moment = $moment;
	}

	function view() {
		$this->smarty->assign('saldisomform', (new SaldiSomForm($this->model, $this->moment))->getHtml());
		$this->smarty->assign('saldisom', $this->model->getSomSaldiOp($this->moment));
		$this->smarty->assign('saldisomleden', $this->model->getSomSaldiOp($this->moment, true));
		$this->smarty->display('fiscaat/saldisommen.tpl');
	}
}
