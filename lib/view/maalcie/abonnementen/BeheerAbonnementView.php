<?php

namespace CsrDelft\view\maalcie\abonnementen;

use CsrDelft\model\entity\maalcie\MaaltijdAbonnement;
use CsrDelft\view\SmartyTemplateView;

class BeheerAbonnementView extends SmartyTemplateView {

	public function __construct(MaaltijdAbonnement $abo) {
		parent::__construct($abo);
	}

	public function view() {
		$this->smarty->assign('abonnement', $this->model);
		$this->smarty->assign('uid', $this->model->uid);
		$this->smarty->assign('vanuid', $this->model->van_uid);
		echo '<td id="maalcie-melding-veld">' . getMelding() . '</td>';
		$this->smarty->display('maalcie/abonnement/beheer_abonnement_veld.tpl');
	}

}
