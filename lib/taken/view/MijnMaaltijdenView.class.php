<?php
namespace Taken\MLT;
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
		return 'Maaltijdenketzer';
	}
	
	public function view() {
		$smarty = new \TemplateEngine();
		$smarty->assign('standaardprijs', sprintf('%.2f', floatval($GLOBALS['standaard_maaltijdprijs'])));
		
		if (is_array($this->_maaltijden)) { // list of maaltijden and list of aanmeldingen
			$smarty->assign('melding', $this->getMelding());
			$smarty->assign('kop', $this->getTitel());
			$smarty->display('taken/menu_pagina.tpl');
			
			$toonlijst = array();
			foreach ($this->_maaltijden as $maaltijd) {
				$mid = $maaltijd->getMaaltijdId();
				$toonlijst[$mid] = MijnMaaltijdenController::magMaaltijdlijstTonen($maaltijd);
				if (!array_key_exists($mid, $this->_aanmeldingen)) {
					$this->_aanmeldingen[$mid] = false;
				}
			}
			$smarty->assign('toonlijst', $toonlijst);
			$smarty->assign('maaltijden', $this->_maaltijden);
			$smarty->assign('aanmeldingen', $this->_aanmeldingen);
			$smarty->display('taken/maaltijd/mijn_maaltijden.tpl');
		}
		else {
			$smarty->assign('toonlijst', MijnMaaltijdenController::magMaaltijdlijstTonen($this->_maaltijden));
			if ($this->_aanmeldingen === null) { // single maaltijd
				$smarty->assign('maaltijd', $this->_maaltijden);
				$smarty->assign('aanmelding', false);
				$smarty->display('taken/maaltijd/mijn_maaltijd_lijst.tpl');
			}
			else { // single aanmelding with maaltijd
				$smarty->assign('maaltijd', $this->_maaltijden);
				$smarty->assign('aanmelding', $this->_aanmeldingen);
				$smarty->display('taken/maaltijd/mijn_maaltijd_lijst.tpl');
			}
		}
	}
}

?>