<?php

/**
 * BeheerTakenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle taken om te beheren.
 * 
 */
class BeheerTakenView extends TemplateView {

	private $_taken;
	private $_maaltijd;
	private $_prullenbak;
	private $_repetities;
	private $_popup;

	public function __construct($taken, $maaltijd = null, $prullenbak = false, $repetities = null, $popup = null) {
		parent::__construct();
		$this->_taken = $taken;
		$this->_maaltijd = $maaltijd;
		$this->_prullenbak = $prullenbak;
		$this->_repetities = $repetities;
		$this->_popup = $popup;
	}

	public function getTitel() {
		if ($this->_maaltijd !== null) {
			return 'Maaltijdcorveebeheer: ' . $this->_maaltijd->getTitel();
		} elseif ($this->_prullenbak) {
			return 'Beheer corveetaken in prullenbak';
		}
		return 'Corveebeheer';
	}

	public function view() {
		if ($this->_maaltijd !== null) {
			$this->smarty->assign('maaltijd', $this->_maaltijd);
		}

		if (is_array($this->_taken)) { // list of corveetaken
			if ($this->_prullenbak || $this->_repetities !== null) { // normal view
				$this->smarty->assign('prullenbak', $this->_prullenbak);
				$this->smarty->assign('popup', $this->_popup);
				$this->smarty->assign('melding', $this->getMelding());
				$this->smarty->assign('kop', $this->getTitel());
				$this->smarty->display('taken/menu_pagina.tpl');

				$takenByDate = array();
				foreach ($this->_taken as $taak) {
					$datum = $taak->getDatum();
					if (!array_key_exists($datum, $takenByDate)) {
						$takenByDate[$datum] = array();
					}
					$takenByDate[$datum][$taak->getFunctieId()][] = $taak;
				}
				if ($this->_maaltijd !== null) {
					$this->smarty->assign('show', true);
				}
				$this->smarty->assign('taken', $takenByDate);
				$this->smarty->assign('repetities', $this->_repetities);
				$this->smarty->display('taken/corveetaak/beheer_taken.tpl');
			} else { // list of new corveetaken
				echo '<tr id="taken-melding"><td>' . $this->getMelding() . '</td></tr>';
				foreach ($this->_taken as $taken) {
					$this->smarty->assign('taak', $taken);
					$this->smarty->assign('show', true);
					$this->smarty->display('taken/corveetaak/beheer_taak_lijst.tpl');
				}
			}
		} elseif (is_int($this->_taken)) { // id of deleted corveetaak
			echo '<tr id="corveetaak-row-' . $this->_taken . '" class="remove"></tr>';
		} else { // single corveetaak
			$this->smarty->assign('taak', $this->_taken);
			$this->smarty->assign('show', true);
			$this->smarty->display('taken/corveetaak/beheer_taak_lijst.tpl');
		}
	}

}

?>