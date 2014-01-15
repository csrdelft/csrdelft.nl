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
		$this->smarty->assign('standaardprijs', sprintf('%.2f', floatval(Instellingen::get('maaltijden', 'standaard_prijs'))));

		if (is_array($this->_maaltijden)) { // list of maaltijden and list of aanmeldingen
			$this->smarty->assign('melding', $this->getMelding());
			$this->smarty->assign('kop', $this->getTitel());
			$this->smarty->display('taken/menu_pagina.tpl');

			$toonlijst = array();
			foreach ($this->_maaltijden as $maaltijd) {
				$mid = $maaltijd->getMaaltijdId();
				$toonlijst[$mid] = MijnMaaltijdenController::magMaaltijdlijstTonen($maaltijd);
				if (!array_key_exists($mid, $this->_aanmeldingen)) {
					$this->_aanmeldingen[$mid] = false;
				}
			}
			$this->smarty->assign('toonlijst', $toonlijst);
			$this->smarty->assign('maaltijden', $this->_maaltijden);
			$this->smarty->assign('aanmeldingen', $this->_aanmeldingen);
			$this->smarty->display('taken/maaltijd/mijn_maaltijden.tpl');
		} else {
			$this->smarty->assign('toonlijst', MijnMaaltijdenController::magMaaltijdlijstTonen($this->_maaltijden));
			if ($this->_aanmeldingen === null) { // single maaltijd
				$this->smarty->assign('maaltijd', $this->_maaltijden);
				$this->smarty->assign('aanmelding', false);
				$this->smarty->display('taken/maaltijd/mijn_maaltijd_lijst.tpl');
			} else { // single aanmelding with maaltijd
				$this->smarty->assign('maaltijd', $this->_maaltijden);
				$this->smarty->assign('aanmelding', $this->_aanmeldingen);
				$this->smarty->display('taken/maaltijd/mijn_maaltijd_lijst.tpl');
			}
		}
	}

}

?>