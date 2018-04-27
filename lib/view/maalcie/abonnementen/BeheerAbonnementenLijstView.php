<?php

namespace CsrDelft\view\maalcie\abonnementen;

use CsrDelft\view\SmartyTemplateView;

class BeheerAbonnementenLijstView extends SmartyTemplateView {

	public function __construct(array $matrix) {
		parent::__construct($matrix);
	}

	public function view() {
		echo '<tr id="maalcie-melding"><td id="maalcie-melding-veld">' . getMelding() . '</td></tr>';
		foreach ($this->model as $vanuid => $abonnementen) {
			$this->smarty->assign('vanuid', $vanuid);
			$this->smarty->assign('abonnementen', $abonnementen);
			$this->smarty->display('maalcie/abonnement/beheer_abonnement_lijst.tpl');
		}
	}

}
