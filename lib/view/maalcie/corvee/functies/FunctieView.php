<?php

namespace CsrDelft\view\maalcie\corvee\functies;

use CsrDelft\model\entity\maalcie\CorveeFunctie;
use CsrDelft\view\SmartyTemplateView;

class FunctieView extends SmartyTemplateView {

	public function __construct(CorveeFunctie $functie) {
		parent::__construct($functie);
	}

	public function view() {
		$this->smarty->assign('functie', $this->model);
		$this->smarty->display('maalcie/functie/beheer_functie_lijst.tpl');
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
	}

}
