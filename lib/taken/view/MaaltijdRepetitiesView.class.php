<?php

/**
 * MaaltijdRepetitiesView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle maaltijd-repetities om te beheren.
 * 
 */
class MaaltijdRepetitiesView extends TemplateView {

	public function getTitel() {
		return 'Beheer maaltijdrepetities';
	}

	public function view() {
		if (is_array($this->model)) { // list of repetities
			$this->smarty->display('taken/menu_pagina.tpl');
			$this->smarty->assign('repetities', $this->model);
			$this->smarty->display('taken/maaltijd-repetitie/beheer_maaltijd_repetities.tpl');
		} elseif (is_int($this->model)) { // id of deleted repetitie
			echo '<tr id="taken-melding"><td>' . SimpleHTML::getMelding() . '</td></tr>';
			echo '<tr id="repetitie-row-' . $this->model . '" class="remove"></tr>';
		} else { // single repetitie
			echo '<tr id="taken-melding"><td>' . SimpleHTML::getMelding() . '</td></tr>';
			$this->smarty->assign('repetitie', $this->model);
			$this->smarty->display('taken/maaltijd-repetitie/beheer_maaltijd_repetitie_lijst.tpl');
		}
	}

}
