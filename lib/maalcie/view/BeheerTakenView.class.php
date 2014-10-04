<?php

/**
 * BeheerTakenView.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Tonen van alle taken om te beheren.
 * 
 */
class BeheerTakenView extends SmartyTemplateView {

	private $maaltijd;
	private $prullenbak;
	private $repetities;

	public function __construct(array $taken, $maaltijd = null, $prullenbak = false, $repetities = null) {
		parent::__construct(array());
		$this->maaltijd = $maaltijd;
		$this->prullenbak = $prullenbak;
		$this->repetities = $repetities;

		if ($this->maaltijd !== null) {
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
	}

	public function view() {
		if ($this->maaltijd !== null) {
			$this->smarty->assign('maaltijd', $this->maaltijd);
			$this->smarty->assign('show', true);
		}
		$this->smarty->assign('taken', $this->model);
		$this->smarty->assign('prullenbak', $this->prullenbak);
		$this->smarty->assign('repetities', $this->repetities);

		$this->smarty->display('maalcie/menu_pagina.tpl');
		$this->smarty->display('maalcie/corveetaak/beheer_taken.tpl');
	}

}

class BeheerTakenLijstView extends SmartyTemplateView {

	public function __construct(array $taken) {
		parent::__construct($taken);
	}

	public function view() {
		$this->smarty->assign('show', true);
		$this->smarty->assign('prullenbak', false);
		echo '<tr id="maalcie-melding"><td>' . getMelding() . '</td></tr>';
		foreach ($this->model as $taak) {
			$this->smarty->assign('taak', $taak);
			$this->smarty->display('maalcie/corveetaak/beheer_taak_lijst.tpl');
		}
	}

}

class BeheerTaakView extends SmartyTemplateView {

	private $maaltijd;

	public function __construct(CorveeTaak $taak, Maaltijd $maaltijd = null) {
		parent::__construct($taak);
		$this->maaltijd = $maaltijd;
	}

	public function view() {
		$this->smarty->assign('taak', $this->model);
		$this->smarty->assign('maaltijd', $this->maaltijd);
		$this->smarty->assign('show', true);
		$this->smarty->assign('prullenbak', false);
		$this->smarty->display('maalcie/corveetaak/beheer_taak_lijst.tpl');
	}

}
