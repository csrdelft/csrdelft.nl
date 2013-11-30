<?php
namespace Taken\CRV;
/**
 * BeheerPuntenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle corveepunten van alle leden.
 * 
 */
class BeheerPuntenView extends \SimpleHtml {

	private $_leden_punten;
	private $_functies;
	
	public function __construct($leden_punten, $functies=null) {
		$this->_leden_punten = $leden_punten;
		$this->_functies = $functies;
	}
	
	public function getTitel() {
		return 'Beheer corveepunten';
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		
		if ($this->_functies === null) { // voor een lid
			$smarty->assign('puntenlijst', $this->_leden_punten);
			$smarty->display('taken/corveepunt/beheer_punten_lijst.tpl');
		}
		else { // matrix of repetities and voorkeuren
			$smarty->assign('melding', $this->getMelding());
			$smarty->assign('kop', $this->getTitel());
			$smarty->display('taken/taken_menu.tpl');
			
			$smarty->assign('matrix', $this->_leden_punten);
			$smarty->assign('functies', $this->_functies);
			$smarty->display('taken/corveepunt/beheer_punten.tpl');
		}
	}
}

?>