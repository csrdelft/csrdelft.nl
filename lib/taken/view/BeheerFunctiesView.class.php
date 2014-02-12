<?php

/**
 * BeheerFunctiesView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle functies om te beheren.
 * 
 */
class BeheerFunctiesView extends TemplateView {

	public function getTitel() {
		return 'Beheer corveefuncties en kwalificaties';
	}

	public function view() {
		if (is_array($this->model)) {
			$this->smarty->display('taken/menu_pagina.tpl');
			$this->smarty->assign('functies', $this->model);
			$this->smarty->display('taken/functie/beheer_functies.tpl');
		} elseif (is_int($this->model)) { // id of deleted corveefunctie
			echo '<tr id="corveefunctie-row-' . $this->model . '" class="remove"></tr>';
		} else {
			$this->smarty->assign('functie', $this->model);
			$this->smarty->display('taken/functie/beheer_functie_lijst.tpl');
		}
	}

}
