<?php
namespace Taken\MLT;
/**
 * MaalCieSaldiView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van een upload tool voor het bijwerken de MaalCie saldi op de stek.
 * 
 */
class MaalCieSaldiView extends \SimpleHtml {
	
	private $_melding;
	
	public function __construct($melding=false) {
		$this->_melding = $melding;
	}
	
	public function getTitel() {
		return 'MaalCie-saldi uploaden met een CSV-bestand';
	}
	
	public function view() {
		if ($this->_melding) {
			echo '<tr id="taken-melding"><td>'. $this->getMelding() .'</td></tr>';
		}
		else {
			$smarty= new \TemplateEngine();
			$smarty->assign('melding', $this->getMelding());
			$smarty->assign('kop', $this->getTitel());
			$smarty->display('taken/menu_pagina.tpl');
			$smarty->display('taken/maalcie_saldi.tpl');
		}
	}
}

?>