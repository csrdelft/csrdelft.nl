<?php

namespace CsrDelft\view\toestemming;
use CsrDelft\view\SmartyTemplateView;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 28/05/2018
 */
class ToestemmingLijstView extends SmartyTemplateView {

	public function view() {
		$this->smarty->assign('toestemmingen', $this->model);
		$this->smarty->display('toestemming/toestemming_lijst.tpl');
	}

	public function getTitel() {
		return "Toestemming overzicht";
	}
}
