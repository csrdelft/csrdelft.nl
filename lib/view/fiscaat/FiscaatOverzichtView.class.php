<?php

namespace CsrDelft\view\fiscaat;

use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\view\SmartyTemplateView;

class FiscaatOverzichtView extends SmartyTemplateView {
	public function view() {
	    $this->smarty->assign('saldisom', CiviSaldoModel::instance()->getSomSaldi());
        $this->smarty->assign('saldisomleden', CiviSaldoModel::instance()->getSomSaldi(true));
		$this->smarty->assign('productenbeheer', new BeheerCiviProductenView());
		$this->smarty->assign('saldobeheer', new BeheerCiviSaldoView());
		$this->smarty->display('fiscaat/overzicht.tpl');
	}

	public function getTitel() {
		return "Civisaldo overzicht";
	}

	public function getBreadcrumbs() {
		return '<a href="/" title="Startpagina"><span class="fa fa-home module-icon"></span></a> » <a href="/fiscaat"><span class="fa fa-eur module-icon"></span></a> » <span class="active">Overzicht</span>';
	}
}
