<?php
namespace Taken\MLT;
/**
 * BeheerInstellingenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle instellingen om te beheren.
 * 
 */
class BeheerInstellingenView extends \SimpleHtml {

	private $_instellingen;
	
	public function __construct($instellingen) {
		$this->_instellingen = $instellingen;
	}
	
	public function getTitel() {
		return 'Beheer instellingen';
	}
	
	public function view() {
		$smarty = new \TemplateEngine();
		
		if (is_array($this->_instellingen)) {
			$smarty->assign('melding', $this->getMelding());
			$smarty->assign('kop', $this->getTitel());
			$smarty->display('taken/menu_pagina.tpl');
			
			$smarty->assign('instellingen', $this->_instellingen);
			$smarty->display('taken/instelling/beheer_instellingen.tpl');
		}
		elseif (is_string($this->_instellingen)) { // id of deleted corveefunctie
			echo '<tr id="instelling-row-'. $this->_instellingen .'" class="remove"></tr>';
		}
		else {
			$smarty->assign('instelling', $this->_instellingen);
			$smarty->display('taken/instelling/beheer_instelling_lijst.tpl');
		}
	}
}

?>