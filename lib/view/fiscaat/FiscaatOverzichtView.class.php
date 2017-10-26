<?php

namespace CsrDelft\view\fiscaat;

use CsrDelft\model\fiscaat\CiviSaldoModel;
use CsrDelft\view\fiscaat\producten\CiviProductTable;
use CsrDelft\view\fiscaat\saldo\CiviSaldoTable;
use CsrDelft\view\fiscaat\saldo\SaldiSomForm;
use CsrDelft\view\SmartyTemplateView;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 04/04/2017
 */
class FiscaatOverzichtView extends SmartyTemplateView {
	public function view() {
		$this->smarty->assign('saldisomform', (new SaldiSomForm(CiviSaldoModel::instance()))->getHtml());
		$this->smarty->assign('saldisom', CiviSaldoModel::instance()->getSomSaldi());
		$this->smarty->assign('saldisomleden', CiviSaldoModel::instance()->getSomSaldi(true));
		$this->smarty->assign('productenbeheer', new CiviProductTable());
		$this->smarty->assign('saldobeheer', new CiviSaldoTable());
		$this->smarty->display('fiscaat/overzicht.tpl');
	}

	public function getTitel() {
		return 'Civisaldo overzicht';
	}

	public function getBreadcrumbs() {
		return '<a href="/" title="Startpagina"><span class="fa fa-home module-icon"></span></a> » <a href="/fiscaat"><span class="fa fa-eur module-icon"></span></a> » <span class="active">Overzicht</span>';
	}
}
