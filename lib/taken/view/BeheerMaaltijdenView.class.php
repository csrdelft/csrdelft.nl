<?php
namespace Taken\MLT;
/**
 * BeheerMaaltijdenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle maaltijden om te beheren.
 * 
 */
class BeheerMaaltijdenView extends \SimpleHtml {

	private $_maaltijden;
	private $_prullenbak;
	private $_archief;
	private $_repetities;
	private $_popup;
	
	public function __construct($maaltijden, $prullenbak=false, $archief=false, $repetities=null, $popup=null) {
		$this->_maaltijden = $maaltijden;
		$this->_prullenbak = $prullenbak;
		$this->_archief = $archief;
		$this->_repetities = $repetities;
		$this->_popup = $popup;
	}
	
	public function getTitel() {
		if ($this->_prullenbak) {
			return 'Beheer maaltijden in prullenbak';
		}
		elseif ($this->_archief) {
			return 'Maaltijdenarchief';
		}
		else {
			return 'Beheer maaltijden';
		}
	}
	
	public function getLidLink($uid) {
		$lid = \LidCache::getLid($uid);
		if ($lid instanceof \Lid) {
			return $lid->getNaamLink($GLOBALS['weergave_ledennamen_beheer'], 'link');
		}
		return $uid;
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		
		if (is_array($this->_maaltijden)) { // list of maaltijden
			if ($this->_prullenbak || $this->_archief || $this->_repetities !== null) { // normal view
				$smarty->assign('prullenbak', $this->_prullenbak);
				$smarty->assign('archief', $this->_archief);
				$smarty->assign('this', $this);
				$smarty->assign('popup', $this->_popup);
				$smarty->assign('melding', $this->getMelding());
				$smarty->assign('kop', $this->getTitel());
				$smarty->display('taken/menu_pagina.tpl');
				
				$smarty->assign('maaltijden', $this->_maaltijden);
				$smarty->assign('repetities', $this->_repetities);
				$smarty->display('taken/maaltijd/beheer_maaltijden.tpl');
			}
			else { // list of new maaltijden
				foreach ($this->_maaltijden as $maaltijd) {
					$smarty->assign('maaltijd', $maaltijd);
					$smarty->display('taken/maaltijd/beheer_maaltijd_lijst.tpl');
				}
			}
		}
		elseif (is_int($this->_maaltijden)) { // id of deleted maaltijd
			echo '<tr id="maaltijd-row-'. $this->_maaltijden .'" class="remove"></tr>';
		}
		else { // single maaltijd
			echo '<tr id="taken-melding"><td>'. $this->getMelding() .'</td></tr>';
			$smarty->assign('maaltijd', $this->_maaltijden);
			$smarty->display('taken/maaltijd/beheer_maaltijd_lijst.tpl');
		}
	}
}

?>