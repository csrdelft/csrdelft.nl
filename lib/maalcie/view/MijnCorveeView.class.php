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
		parent::__construct($taken);
		$this->titel = 'Mijn corveeoverzicht';
		$this->smarty->assign('rooster', $this->model);
		$this->smarty->assign('puntenlijst', $punten);
		$this->smarty->assign('functies', $functies);
		$this->smarty->assign('vrijstelling', $vrijstelling);
		$this->smarty->assign('toonverleden', false);
	}

	public function view() {
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/corveetaak/mijn_rooster.tpl');
		$this->smarty->display('maalcie/corveepunt/mijn_punten.tpl');
		$this->smarty->display('maalcie/vrijstelling/mijn_vrijstelling.tpl');
	}

}
