<?php

/**
 * MijnCorveeView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van de corveepunten, vrijstellingen en corveetaken van een lid.
 * 
 */
class MijnCorveeView extends TemplateView {

	public function __construct(array $taken, array $punten, array $functies, CorveeVrijstelling $vrijstelling = null) {
		parent::__construct();
		$this->titel = 'Mijn corveeoverzicht';
		$this->smarty->assign('rooster', $taken);
		$this->smarty->assign('puntenlijst', $punten);
		$this->smarty->assign('functies', $functies);
		$this->smarty->assign('vrijstelling', $vrijstelling);
	}

	public function view() {
		$this->smarty->display('taken/menu_pagina.tpl');
		$this->smarty->display('taken/corveetaak/mijn_rooster.tpl');
		$this->smarty->display('taken/corveepunt/mijn_punten.tpl');
		$this->smarty->display('taken/vrijstelling/mijn_vrijstelling.tpl');
	}

}
