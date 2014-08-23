<?php

/**
 * MijnCorveeView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van de corveepunten, vrijstellingen en corveetaken van een lid.
 * 
 */
class MijnCorveeView extends SmartyTemplateView {

	private $punten;
	private $functies;
	private $vrijstelling;

	public function __construct(array $taken, array $punten, array $functies, CorveeVrijstelling $vrijstelling = null) {
		parent::__construct($taken, 'Mijn corveeoverzicht');
		$this->punten = $punten;
		$this->functies = $functies;
		$this->vrijstelling = $vrijstelling;
	}

	public function view() {
		$this->smarty->assign('rooster', $this->model);
		$this->smarty->assign('puntenlijst', $this->punten);
		$this->smarty->assign('functies', $this->functies);
		$this->smarty->assign('vrijstelling', $this->vrijstelling);
		$this->smarty->assign('toonverleden', false);

		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/corveetaak/mijn_rooster.tpl');
		$this->smarty->display('maalcie/corveepunt/mijn_punten.tpl');
		$this->smarty->display('maalcie/vrijstelling/mijn_vrijstelling.tpl');
	}

}
