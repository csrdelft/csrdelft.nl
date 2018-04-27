<?php

namespace CsrDelft\view\maalcie\corvee\taken;

use CsrDelft\view\SmartyTemplateView;

class BeheerTakenLijstView extends SmartyTemplateView {

	public function __construct(array $taken) {
		parent::__construct($taken);
	}

	public function view() {
		$this->smarty->assign('show', true);
		$this->smarty->assign('prullenbak', false);
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
		foreach ($this->model as $taak) {
			$this->smarty->assign('taak', $taak);
			$this->smarty->display('maalcie/corveetaak/beheer_taak_lijst.tpl');
		}
	}

}
