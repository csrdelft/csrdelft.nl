<?php

namespace CsrDelft\view\maalcie\persoonlijk\abonnementen;

use CsrDelft\model\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\view\SmartyTemplateView;

class MijnAbonnementView extends SmartyTemplateView {

	public function __construct(MaaltijdAbonnement $abo) {
		parent::__construct($abo);
	}

	public function view() {
		$this->smarty->assign('uid', $this->model->uid);
		$this->smarty->assign('mrid', $this->model->mlt_repetitie_id);
		echo '<td id="maalcie-melding-veld">' . getMelding() . '</td>';
		$this->smarty->display('maalcie/abonnement/mijn_abonnement_veld.tpl');
	}

}
