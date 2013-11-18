<?php
namespace Taken\CRV;
/**
 * BeheerFunctiesView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle functies om te beheren.
 * 
 */
class BeheerFunctiesView extends \SimpleHtml {

	private $_functies;
	private $_popup;
	
	public function __construct($functies, $popup=null) {
		$this->_functies = $functies;
		$this->_popup = $popup;
	}
	
	public function getTitel() {
		return 'Beheer corveefuncties en kwalificaties';
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		$smarty->assign('module', '/actueel/taken/functies');
		
		if (is_array($this->_functies)) {
			$smarty->assign('popup', $this->_popup);
			$smarty->assign('melding', $this->getMelding());
			$smarty->assign('kop', $this->getTitel());
			$smarty->display('taken/taken_menu.tpl');
			
			$smarty->assign('functies', $this->_functies);
			$smarty->display('taken/functie/beheer_functies.tpl');
		}
		elseif (is_int($this->_functies)) { // id of deleted corveefunctie
			echo '<tr id="corveefunctie-row-'. $this->_functies .'" class="remove"></tr>';
		}
		else {
			$smarty->assign('functie', $this->_functies);
			$smarty->display('taken/functie/beheer_functie_lijst.tpl');
		}
	}
}

?>