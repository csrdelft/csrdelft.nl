<?php

/**
 * BeheerMaaltijdenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle maaltijden om te beheren.
 * 
 */
class BeheerMaaltijdenView extends TemplateView {

	public function __construct(array $maaltijden, $prullenbak = false, $archief = false, $repetities = null) {
		parent::__construct($maaltijden);

		if ($prullenbak) {
			$this->titel = 'Beheer maaltijden in prullenbak';
		} elseif ($archief) {
			$this->titel = 'Maaltijdenarchief';
		} else {
			$this->titel = 'Beheer maaltijden';
		}

		$this->smarty->assign('maaltijden', $this->model);
		$this->smarty->assign('prullenbak', $prullenbak);
		$this->smarty->assign('archief', $archief);
		$this->smarty->assign('repetities', $repetities);
	}

	public function view() {
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/maaltijd/beheer_maaltijden.tpl');
	}

}

class BeheerMaaltijdenLijstView extends TemplateView {

	public function __construct(array $maaltijden) {
		parent::__construct($maaltijden);
		$this->smarty->assign('prullenbak', false);
	}

	public function view() {
		echo '<tr id="maalcie-melding"><td>' . SimpleHTML::getMelding() . '</td></tr>';
		foreach ($this->model as $maaltijd) {
			$this->smarty->assign('maaltijd', $maaltijd);
			$this->smarty->display('maalcie/maaltijd/beheer_maaltijd_lijst.tpl');
		}
	}

}

class BeheerMaaltijdView extends TemplateView {

	public function __construct(Maaltijd $maaltijd, $prullenbak = false) {
		parent::__construct($maaltijd);
		$this->smarty->assign('maaltijd', $this->model);
		$this->smarty->assign('prullenbak', $prullenbak);
	}

	public function view() {
		echo '<tr id="maalcie-melding"><td>' . SimpleHTML::getMelding() . '</td></tr>';
		$this->smarty->display('maalcie/maaltijd/beheer_maaltijd_lijst.tpl');
	}

}
