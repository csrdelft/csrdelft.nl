<?php

/**
 * BeheerTakenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle taken om te beheren.
 * 
 */
class BeheerTakenView extends TemplateView {

	public function __construct(array $taken, $maaltijd = null, $prullenbak = false, $repetities = null) {
		parent::__construct(array());

		if ($maaltijd !== null) {
			$this->smarty->assign('maaltijd', $maaltijd);
			$this->smarty->assign('show', true);

			$this->titel = 'Maaltijdcorveebeheer: ' . $maaltijd->getTitel();
		} elseif ($prullenbak) {
			$this->titel = 'Beheer corveetaken in prullenbak';
		} else {
			$this->titel = 'Corveebeheer';
		}

		foreach ($taken as $taak) {
			$datum = $taak->getDatum();
			if (!array_key_exists($datum, $this->model)) {
				$this->model[$datum] = array();
			}
			$this->model[$datum][$taak->getFunctieId()][] = $taak;
		}
		$this->smarty->assign('taken', $this->model);
		$this->smarty->assign('prullenbak', $prullenbak);
		$this->smarty->assign('repetities', $repetities);
	}

	public function view() {
		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/corveetaak/beheer_taken.tpl');
	}

}

class BeheerTakenLijstView extends TemplateView {

	public function __construct(array $taken) {
		parent::__construct($taken);
		$this->smarty->assign('show', true);
		$this->smarty->assign('prullenbak', false);
	}

	public function view() {
		echo '<tr id="maalcie-melding"><td>' . SimpleHTML::getMelding() . '</td></tr>';
		foreach ($this->model as $taak) {
			$this->smarty->assign('taak', $taak);
			$this->smarty->display('maalcie/corveetaak/beheer_taak_lijst.tpl');
		}
	}

}

class BeheerTaakView extends TemplateView {

	public function __construct(CorveeTaak $taak, Maaltijd $maaltijd = null) {
		parent::__construct($taak);
		$this->smarty->assign('taak', $this->model);
		$this->smarty->assign('maaltijd', $maaltijd);
		$this->smarty->assign('show', true);
		$this->smarty->assign('prullenbak', false);
	}

	public function view() {
		$this->smarty->display('maalcie/corveetaak/beheer_taak_lijst.tpl');
	}

}
