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

	public function __construct(Maaltijd $maaltijd, $aanmelding = null) {
		parent::__construct($maaltijd, 'Maaltijdketzer');

		$this->smarty->assign('standaardprijs', floatval(Instellingen::get('maaltijden', 'standaard_prijs')));
		$this->smarty->assign('maaltijd', $this->model);
		$this->smarty->assign('aanmelding', $aanmelding);
		$this->smarty->assign('toonlijst', MijnMaaltijdenController::magMaaltijdlijstTonen($this->model));
	}

	public function getKetzer() {
		return $this->smarty->fetch('taken/maaltijd/maaltijd_ketzer.tpl');
	}

	public function view() {
		echo $this->getKetzer();
	}

}
