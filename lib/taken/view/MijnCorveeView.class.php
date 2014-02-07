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
		$this->smarty->display('taken/menu_pagina.tpl');

		$this->smarty->assign('rooster', $this->_rooster);
		$this->smarty->display('taken/corveetaak/mijn_rooster.tpl');

		$this->smarty->assign('puntenlijst', $this->_punten);
		$this->smarty->assign('functies', $this->_functies);
		$this->smarty->display('taken/corveepunt/mijn_punten.tpl');

		$this->smarty->assign('vrijstelling', $this->_vrijstelling);
		$this->smarty->display('taken/vrijstelling/mijn_vrijstelling.tpl');
	}

}

?>