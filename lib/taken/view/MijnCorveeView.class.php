<?php

/**
 * MijnCorveeView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van de corveepunten, vrijstellingen en corveetaken van een lid.
 * 
 */
class MijnCorveeView extends TemplateView {

	private $_rooster;
	private $_punten;
	private $_functies;
	private $_vrijstelling;

	public function __construct($taken, $punten, $functies, $vrijstelling) {
		parent::__construct();
		$this->_rooster = $taken;
		$this->_punten = $punten;
		$this->_functies = $functies;
		$this->_vrijstelling = $vrijstelling;
	}

	public function getTitel() {
		return 'Mijn corveeoverzicht';
	}

	public function view() {
		$this->assign('melding', $this->getMelding());
		$this->assign('kop', $this->getTitel());
		$this->display('taken/menu_pagina.tpl');

		$this->assign('rooster', $this->_rooster);
		$this->display('taken/corveetaak/mijn_rooster.tpl');

		$this->assign('puntenlijst', $this->_punten);
		$this->assign('functies', $this->_functies);
		$this->display('taken/corveepunt/mijn_punten.tpl');

		$this->assign('vrijstelling', $this->_vrijstelling);
		$this->display('taken/vrijstelling/mijn_vrijstelling.tpl');
	}

}

?>