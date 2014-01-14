<?php

/**
 * MijnMaaltijdenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van komende maaltijden en of een lid zich heeft aangemeld.
 * 
 */
class MijnMaaltijdenView extends TemplateView {

	private $_maaltijden;
	private $_aanmeldingen;

	public function __construct($maaltijden, $aanmeldingen = null) {
		parent::__construct();
		$this->_maaltijden = $maaltijden;
		$this->_aanmeldingen = $aanmeldingen;
	}

	public function getTitel() {
		return 'Maaltijdenketzer';
	}

	public function view() {
		$this->assign('standaardprijs', sprintf('%.2f', floatval($GLOBALS['maaltijden']['standaard_maaltijdprijs'])));

		if (is_array($this->_maaltijden)) { // list of maaltijden and list of aanmeldingen
			$this->assign('melding', $this->getMelding());
			$this->assign('kop', $this->getTitel());
			$this->display('taken/menu_pagina.tpl');

			$toonlijst = array();
			foreach ($this->_maaltijden as $maaltijd) {
				$mid = $maaltijd->getMaaltijdId();
				$toonlijst[$mid] = MijnMaaltijdenController::magMaaltijdlijstTonen($maaltijd);
				if (!array_key_exists($mid, $this->_aanmeldingen)) {
					$this->_aanmeldingen[$mid] = false;
				}
			}
			$this->assign('toonlijst', $toonlijst);
			$this->assign('maaltijden', $this->_maaltijden);
			$this->assign('aanmeldingen', $this->_aanmeldingen);
			$this->display('taken/maaltijd/mijn_maaltijden.tpl');
		} else {
			$this->assign('toonlijst', MijnMaaltijdenController::magMaaltijdlijstTonen($this->_maaltijden));
			if ($this->_aanmeldingen === null) { // single maaltijd
				$this->assign('maaltijd', $this->_maaltijden);
				$this->assign('aanmelding', false);
				$this->display('taken/maaltijd/mijn_maaltijd_lijst.tpl');
			} else { // single aanmelding with maaltijd
				$this->assign('maaltijd', $this->_maaltijden);
				$this->assign('aanmelding', $this->_aanmeldingen);
				$this->display('taken/maaltijd/mijn_maaltijd_lijst.tpl');
			}
		}
	}

}

?>