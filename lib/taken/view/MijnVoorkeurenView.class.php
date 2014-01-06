<?php
namespace Taken\CRV;
/**
 * MijnVoorkeurenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van voorkeuren die een lid aan of uit kan zetten.
 * 
 */
class MijnVoorkeurenView extends \SimpleHtml {

	private $_voorkeuren;
	private $_eetwens;
	
	public function __construct($voorkeuren=null, $eetwens=null) {
		$this->_voorkeuren = $voorkeuren;
		$this->_eetwens = $eetwens;
	}
	
	public function getTitel() {
		return 'Mijn voorkeuren';
	}
	
	public function view() {
		$smarty = new \Smarty3CSR();
		
		if ($this->_voorkeuren === null) { // eetwens
			$smarty->assign('eetwens', $this->_eetwens);
			$smarty->display('taken/voorkeur/mijn_eetwens.tpl');
		}
		elseif (is_array($this->_voorkeuren)) { // list of voorkeuren
			$smarty->assign('melding', $this->getMelding());
			$smarty->assign('kop', $this->getTitel());
			$smarty->display('taken/menu_pagina.tpl');
			
			$smarty->assign('eetwens', $this->_eetwens);
			$smarty->assign('voorkeuren', $this->_voorkeuren);
			$smarty->display('taken/voorkeur/mijn_voorkeuren.tpl');
		}
		elseif (is_int($this->_voorkeuren)) { // id of disabled voorkeur
			$smarty->assign('crid', $this->_voorkeuren);
			$smarty->display('taken/voorkeur/mijn_voorkeur_veld.tpl');
		}
		else { // single voorkeur
			$smarty->assign('uid', $this->_voorkeuren->getLidId());
			$smarty->assign('crid', $this->_voorkeuren->getCorveeRepetitieId());
			$smarty->display('taken/voorkeur/mijn_voorkeur_veld.tpl');
		}
	}
}

?>