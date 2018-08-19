<?php

namespace CsrDelft\view\maalcie\corvee\repetities;

use CsrDelft\model\entity\maalcie\CorveeRepetitie;
use CsrDelft\view\SmartyTemplateView;

class CorveeRepetitieView extends SmartyTemplateView {

	public function __construct(CorveeRepetitie $repetitie) {
		parent::__construct($repetitie);
	}

	public function view() {
		$this->smarty->assign('repetitie', $this->model);
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
		$this->smarty->display('maalcie/corvee-repetitie/beheer_corvee_repetitie_lijst.tpl');
	}

}
