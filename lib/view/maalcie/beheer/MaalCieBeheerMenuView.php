<?php

namespace CsrDelft\view\maalcie\beheer;

use CsrDelft\model\security\LoginModel;
use CsrDelft\view\SmartyTemplateView;

/**
 * MaalCieBeheerMenuView.php
 *
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Tonen van beheermenu voor corvee en maaltijden in de zijbalk.
 *
 */
class MaalCieBeheerMenuView extends SmartyTemplateView {

	public function view() {
		if (($this->getModel() === 'corvee' AND LoginModel::mag(P_CORVEE_MOD)) OR ($this->getModel() === 'maaltijden' AND LoginModel::mag(P_MAAL_MOD))) {
			$this->smarty->display('maalcie/menu_beheer_' . $this->getModel() . '.tpl');
		}
	}

}
