<?php
namespace Taken\CRV;
/**
 * BeheerTakenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle taken om te beheren.
 * 
 */
class BeheerTakenView extends \SimpleHtml {

	private $_taken;
	private $_maaltijd;
	private $_prullenbak;
	private $_repetities;
	private $_popup;
	
	public function __construct($taken, $maaltijd=null, $prullenbak=false, $repetities=null, $popup=null) {
		$this->_taken = $taken;
		$this->_maaltijd = $maaltijd;
		$this->_prullenbak = $prullenbak;
		$this->_repetities = $repetities;
		$this->_popup = $popup;
	}
	
	public function getTitel() {
		if ($this->_maaltijd !== null) {
			return 'Maaltijdcorveebeheer: '. $this->_maaltijd->getTitel();
		}
		elseif ($this->_prullenbak) {
			return 'Beheer corveetaken in prullenbak';
		}
		return 'Corveebeheer';
	}
	
	public function view() {
		$smarty = new \Smarty3CSR();
		
		if ($this->_maaltijd !== null) {
			$smarty->assign('maaltijd', $this->_maaltijd);
		}
		
		if (is_array($this->_taken)) { // list of corveetaken
			if ($this->_prullenbak || $this->_repetities !== null) { // normal view
				$smarty->assign('prullenbak', $this->_prullenbak);
				$smarty->assign('popup', $this->_popup);
				$smarty->assign('melding', $this->getMelding());
				$smarty->assign('kop', $this->getTitel());
				$smarty->display('taken/menu_pagina.tpl');
				
				$takenByDate = array();
				foreach ($this->_taken as $taak) {
					$datum = $taak->getDatum();
					if (!array_key_exists($datum, $takenByDate)) {
						$takenByDate[$datum] = array();
					}
					$takenByDate[$datum][$taak->getFunctieId()][] = $taak;
				}
				if ($this->_maaltijd !== null) {
					$smarty->assign('show', true);
				}
				$smarty->assign('taken', $takenByDate);
				$smarty->assign('repetities', $this->_repetities);
				$smarty->display('taken/corveetaak/beheer_taken.tpl');
			}
			else { // list of new corveetaken
				echo '<tr id="taken-melding"><td>'. $this->getMelding() .'</td></tr>';
				foreach ($this->_taken as $taken) {
					$smarty->assign('taak', $taken);
					$smarty->assign('show', true);
					$smarty->display('taken/corveetaak/beheer_taak_lijst.tpl');
				}
			}
		}
		elseif (is_int($this->_taken)) { // id of deleted corveetaak
			echo '<tr id="corveetaak-row-'. $this->_taken .'" class="remove"></tr>';
		}
		else { // single corveetaak
			$smarty->assign('taak', $this->_taken);
			$smarty->assign('show', true);
			$smarty->display('taken/corveetaak/beheer_taak_lijst.tpl');
		}
	}
}

?>