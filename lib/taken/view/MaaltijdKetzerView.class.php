<?php

require_once 'taken/controller/MijnMaaltijdenController.class.php';

/**
 * MaaltijdKetzerView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van een ketzer voor een specifieke maaltijd waarmee een lid zich kan aan- of afmelden voor die maaltijd.
 * 
 */
class MaaltijdKetzerView extends TemplateView {

	private $aanmelding;

	public function __construct(Maaltijd $maaltijd, $aanmelding = null) {
		parent::__construct($maaltijd);
		$this->aanmelding = $aanmelding;
	}

	public function getTitel() {
		return 'Maaltijdketzer';
	}

	public function getKetzer() {
		$this->smarty->assign('standaardprijs', floatval(Instellingen::get('maaltijden', 'standaard_prijs')));
		$this->smarty->assign('maaltijd', $this->model);
		$this->smarty->assign('aanmelding', $this->aanmelding);
		$this->smarty->assign('toonlijst', MijnMaaltijdenController::magMaaltijdlijstTonen($this->model));
		return $this->smarty->fetch('taken/maaltijd/maaltijd_ketzer.tpl');
	}

	public function view() {
		echo $this->getKetzer();
	}

}
