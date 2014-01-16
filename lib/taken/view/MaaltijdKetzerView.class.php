<?php

require_once 'taken/controller/MijnMaaltijdenController.class.php';

/**
 * MaaltijdKetzerView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van een ketzer voor een specifieke maaltijd waarmee een lid zich kan aan- of afmelden voor die maaltijd.
 * 
 */
class MaaltijdKetzerView extends TemplateView {

	private $_maaltijd;
	private $_aanmelding;

	public function __construct($maaltijd, $aanmelding = null) {
		parent::__construct();
		$this->_maaltijd = $maaltijd;
		$this->_aanmelding = $aanmelding;
	}

	public function getTitel() {
		return 'Maaltijdketzer';
	}

	public function viewReturn() {
		$this->smarty->assign('maaltijd', $this->_maaltijd);
		$this->smarty->assign('aanmelding', $this->_aanmelding);
		$this->smarty->assign('toonlijst', MijnMaaltijdenController::magMaaltijdlijstTonen($this->_maaltijd));
		return $this->smarty->fetch('taken/maaltijd/maaltijd_ketzer.tpl');
	}

	public function view() {
		echo $this->viewReturn();
	}

}

?>