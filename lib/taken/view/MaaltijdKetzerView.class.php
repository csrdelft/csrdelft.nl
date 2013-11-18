<?php
namespace Taken\MLT;
/**
 * MaaltijdKetzerView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van een ketzer voor een specifieke maaltijd waarmee een lid zich kan aan- of afmelden voor die maaltijd.
 * 
 */
class MaaltijdKetzerView extends \SimpleHtml {

	private $_maaltijd;
	private $_aanmelding;
	
	public function __construct($maaltijd, $aanmelding=null) {
		$this->_maaltijd = $maaltijd;
		$this->_aanmelding = $aanmelding;
	}
	
	public function getTitel() {
		return 'Maaltijdketzer';
	}
	
	public function view() {
		$this->_smarty = new \Smarty_csr();
		$this->_smarty->assign('maaltijd', $this->_maaltijd);
		$this->_smarty->assign('aanmelding', $this->_aanmelding);
		$this->_smarty->display('taken/maaltijd/maaltijd_ketzer.tpl');
	}
}

?>