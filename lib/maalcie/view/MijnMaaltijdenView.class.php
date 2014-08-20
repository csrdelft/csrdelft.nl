<?php

/**
 * MijnMaaltijdenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van komende maaltijden en of een lid zich heeft aangemeld.
 * 
 */
class MijnMaaltijdenView extends SmartyTemplateView {

	public function __construct(array $maaltijden, array $aanmeldingen = null) {
		parent::__construct($maaltijden, 'Maaltijdenketzer');

		foreach ($this->model as $maaltijd) {
			$mid = $maaltijd->getMaaltijdId();
			if (!array_key_exists($mid, $aanmeldingen)) {
				$aanmeldingen[$mid] = false;
			}
		}
		$this->smarty->assign('standaardprijs', sprintf('%.2f', floatval(Instellingen::get('maaltijden', 'standaard_prijs'))));
		$this->smarty->assign('maaltijden', $this->model);
		$this->smarty->assign('aanmeldingen', $aanmeldingen);
	}

	public function view() {
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/maaltijd/mijn_maaltijden.tpl');
	}

}

class MijnMaaltijdView extends SmartyTemplateView {

	public function __construct(Maaltijd $maaltijd, MaaltijdAanmelding $aanmelding = null) {
		parent::__construct($maaltijd);
		$this->smarty->assign('maaltijd', $this->model);
		$this->smarty->assign('aanmelding', $aanmelding);
		$this->smarty->assign('standaardprijs', sprintf('%.2f', floatval(Instellingen::get('maaltijden', 'standaard_prijs'))));
	}

	public function view() {
		$this->smarty->display('maalcie/maaltijd/mijn_maaltijd_lijst.tpl');
	}

}
