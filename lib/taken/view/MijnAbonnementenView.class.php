<?php
namespace Taken\MLT;
/**
 * MijnAbonnementenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van abonnementen die een lid aan of uit kan zetten.
 * 
 */
class MijnAbonnementenView extends \SimpleHtml {

	private $_abonnementen;
	
	public function __construct($abonnementen) {
		$this->_abonnementen = $abonnementen;
	}
	
	public function getTitel() {
		return 'Mijn abonnementen';
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		
		if (is_array($this->_abonnementen)) { // list of abonnementen
			$smarty->assign('melding', $this->getMelding());
			$smarty->assign('kop', $this->getTitel());
			$smarty->display('taken/menu_pagina.tpl');
			
			$smarty->assign('abonnementen', $this->_abonnementen);
			$smarty->display('taken/abonnement/mijn_abonnementen.tpl');
		}
		elseif (is_int($this->_abonnementen)) { // id of disabled abonnement
			echo '<td id="taken-melding-veld">'. $this->getMelding() .'</td>';
			$smarty->assign('mrid', $this->_abonnementen);
			$smarty->display('taken/abonnement/mijn_abonnement_veld.tpl');
		}
		else { // single abonnement
			echo '<td id="taken-melding-veld">'. $this->getMelding() .'</td>';
			$smarty->assign('uid', $this->_abonnementen->getLidId());
			$smarty->assign('mrid', $this->_abonnementen->getMaaltijdRepetitieId());
			$smarty->display('taken/abonnement/mijn_abonnement_veld.tpl');
		}
	}
}

?>