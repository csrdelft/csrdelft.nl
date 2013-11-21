<?php
namespace Taken\MLT;

require_once 'taken/controller/BeheerMaaltijdenController.class.php';

/**
 * MijnMaaltijdenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van komende maaltijden en of een lid zich heeft aangemeld.
 * 
 */
class MijnMaaltijdenView extends \SimpleHtml {

	private $_maaltijden;
	private $_aanmeldingen;
	
	public function __construct($maaltijden, $aanmeldingen=null) {
		$this->_maaltijden = $maaltijden;
		$this->_aanmeldingen = $aanmeldingen;
	}
	
	public function getTitel() {
		return 'Maaltijdketzers';
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		$smarty->assign('module', '/actueel/taken/maaltijden');
		$smarty->assign('toonlijst', BeheerMaaltijdenController::magMaaltijdlijstTonen());
		
		if (is_array($this->_aanmeldingen)) { // list of aanmeldingen and list of maaltijden
			$smarty->assign('melding', $this->getMelding());
			$smarty->assign('kop', $this->getTitel());
			$smarty->display('taken/taken_menu.tpl');
			
			$smarty->assign('maaltijden', $this->_maaltijden);
			$smarty->assign('aanmeldingen', $this->_aanmeldingen);
			$smarty->display('taken/maaltijd/mijn_maaltijden.tpl');
		}
		elseif ($this->_aanmeldingen === null) { // single maaltijd
			$smarty->assign('maaltijd', $this->_maaltijden);
			$smarty->display('taken/maaltijd/mijn_maaltijd_lijst.tpl');
		}
		else { // single aanmelding with maaltijd
			$smarty->assign('maaltijd', $this->_maaltijden);
			$smarty->assign('aanmelding', $this->_aanmeldingen);
			$smarty->display('taken/maaltijd/mijn_maaltijd_lijst.tpl');
		}
	}
}

?>