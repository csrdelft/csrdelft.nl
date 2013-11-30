<?php
namespace Taken\CRV;
/**
 * CorveeRoosterView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van het corveerooster.
 * 
 */
class CorveeRoosterView extends \SimpleHtml {

	private $_rooster;
	
	public function __construct($rooster) {
		$this->_rooster = $rooster;
	}
	
	public function getTitel() {
		return 'Corveerooster';
	}
	
	public function view() {
		$smarty= new \Smarty_csr();
		
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('kop', $this->getTitel());
		$smarty->display('taken/taken_menu.tpl');
		
		$smarty->assign('rooster', $this->_rooster);
		$smarty->display('taken/corveetaak/corvee_rooster.tpl');
	}
}

?>