<?php

/**
 * BeheerTakenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle taken om te beheren.
 * 
 */
class BeheerTakenView extends TemplateView {

	private $maaltijd;
	private $prullenbak;
	private $repetities;

	public function __construct($taken, $maaltijd = null, $prullenbak = false, $repetities = null) {
		parent::__construct($taken);
		$this->maaltijd = $maaltijd;
		$this->prullenbak = $prullenbak;
		$this->repetities = $repetities;
	}

	public function getTitel() {
		if ($this->maaltijd !== null) {
			return 'Maaltijdcorveebeheer: ' . $this->maaltijd->getTitel();
		} elseif ($this->prullenbak) {
			return 'Beheer corveetaken in prullenbak';
		}
		return 'Corveebeheer';
	}

	public function view() {
		if ($this->maaltijd !== null) {
			$this->smarty->assign('maaltijd', $this->maaltijd);
		}
		if (is_array($this->model)) { // list of corveetaken
			if ($this->prullenbak || $this->repetities !== null) { // normal view
				$this->smarty->assign('prullenbak', $this->prullenbak);
				$this->smarty->display('taken/menu_pagina.tpl');
				$takenByDate = array();
				foreach ($this->model as $taak) {
					$datum = $taak->getDatum();
					if (!array_key_exists($datum, $takenByDate)) {
						$takenByDate[$datum] = array();
					}
					$takenByDate[$datum][$taak->getFunctieId()][] = $taak;
				}
				if ($this->maaltijd !== null) {
					$this->smarty->assign('show', true);
				}
				$this->smarty->assign('taken', $takenByDate);
				$this->smarty->assign('repetities', $this->repetities);
				$this->smarty->display('taken/corveetaak/beheer_taken.tpl');
			} else { // list of new corveetaken
				echo '<tr id="taken-melding"><td>' . SimpleHTML::getMelding() . '</td></tr>';
				foreach ($this->model as $taken) {
					$this->smarty->assign('taak', $taken);
					$this->smarty->assign('show', true);
					$this->smarty->display('taken/corveetaak/beheer_taak_lijst.tpl');
				}
			}
		} elseif (is_int($this->model)) { // id of deleted corveetaak
			echo '<tr id="corveetaak-row-' . $this->model . '" class="remove"></tr>';
		} else { // single corveetaak
			$this->smarty->assign('taak', $this->model);
			$this->smarty->assign('show', true);
			$this->smarty->display('taken/corveetaak/beheer_taak_lijst.tpl');
		}
	}

}
