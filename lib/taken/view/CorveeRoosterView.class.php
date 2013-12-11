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
	private $_toonverleden;
	
	public function __construct($rooster, $toonverleden=false) {
		$this->_rooster = $rooster;
		$this->_toonverleden = $toonverleden;
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
		$smarty->assign('toonverleden', $this->_toonverleden);
		$smarty->display('taken/corveetaak/corvee_rooster.tpl');
	}
}

?>