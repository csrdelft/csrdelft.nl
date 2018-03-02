<?php

namespace CsrDelft\view\fiscaat\pin;

use CsrDelft\view\SmartyTemplateView;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @date 19/09/2017
 */
class PinTransactieOverzichtView extends SmartyTemplateView {
	public function __construct() {
		parent::__construct(null);
	}

	public function getBreadcrumbs() {
		return '<a href="/" title="Startpagina"><span class="fa fa-home module-icon"></span> Thuis</a> » <a href="/fiscaat"><span class="fa fa-eur module-icon"></span> Fiscaat</a> » <span class="active">Pin Transacties</span>';
	}

	public function getTitel() {
		return 'Pin transacties';
	}

	public function view() {
		$this->smarty->assign('pinTransactieMatchTable', new PinTransactieMatchTable());
		$this->smarty->display('fiscaat/overzicht_pin.tpl');
	}
}
