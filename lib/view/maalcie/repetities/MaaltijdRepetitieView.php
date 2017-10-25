<?php

namespace CsrDelft\view\maalcie\repetities;

use CsrDelft\model\entity\maalcie\MaaltijdRepetitie;
use CsrDelft\view\SmartyTemplateView;

class MaaltijdRepetitieView extends SmartyTemplateView {

	public function __construct(MaaltijdRepetitie $repetitie) {
		parent::__construct($repetitie);
	}

	public function view() {
		$this->smarty->assign('repetitie', $this->model);
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
		$this->smarty->display('maalcie/maaltijd-repetitie/beheer_maaltijd_repetitie_lijst.tpl');
	}

}
