<?php
namespace Taken\CRV;
/**
 * BeheerVrijstellingenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle vrijstellingen om te beheren.
 * 
 */
class BeheerVrijstellingenView extends \SimpleHtml {

	private $_vrijstellingen;
	
	public function __construct($vrijstellingen) {
		$this->_vrijstellingen = $vrijstellingen;
	}
	
	public function getTitel() {
		return 'Beheer vrijstellingen';
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		
		if (is_array($this->_vrijstellingen)) {
			$smarty->assign('melding', $this->getMelding());
			$smarty->assign('kop', $this->getTitel());
			$smarty->display('taken/taken_menu.tpl');
			
			$smarty->assign('vrijstellingen', $this->_vrijstellingen);
			$smarty->display('taken/vrijstelling/beheer_vrijstellingen.tpl');
		}
		elseif (is_string($this->_vrijstellingen)) { // id of deleted corveefunctie
			echo '<tr id="vrijstelling-row-'. $this->_vrijstellingen .'" class="remove"></tr>';
		}
		else {
			$smarty->assign('vrijstelling', $this->_vrijstellingen);
			$smarty->display('taken/vrijstelling/beheer_vrijstelling_lijst.tpl');
		}
	}
}

?>