<?php
namespace Taken\CRV;
/**
 * BeheerVoorkeurenView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle voorkeuren van alle leden.
 * 
 */
class BeheerVoorkeurenView extends \SimpleHtml {

	private $_leden_voorkeuren;
	private $_repetities;
	
	public function __construct($leden_voorkeuren, $repetities=null) {
		$this->_leden_voorkeuren = $leden_voorkeuren;
		$this->_repetities = $repetities;
	}
	
	public function getTitel() {
		return 'Beheer voorkeuren';
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		$smarty->assign('ledenweergave', $GLOBALS['weergave_ledennamen_beheer']);
		
		if ($this->_repetities === null) { // voor een lid
			if (is_array($this->_leden_voorkeuren)) { // lijst van voorkeuren
				$smarty->assign('voorkeuren', $this->_leden_voorkeuren);
				$smarty->display('taken/voorkeur/beheer_voorkeur_lijst.tpl');
			}
			else { // voorkeur aan/afmelding
				$smarty->assign('voorkeur', $this->_leden_voorkeuren);
				$smarty->assign('crid', $this->_leden_voorkeuren->getCorveeRepetitieId());
				$smarty->assign('uid', $this->_leden_voorkeuren->getLidId());
				$smarty->display('taken/voorkeur/beheer_voorkeur_veld.tpl');
			}
		}
		else { // matrix of repetities and voorkeuren
			$smarty->assign('melding', $this->getMelding());
			$smarty->assign('kop', $this->getTitel());
			$smarty->display('taken/taken_menu.tpl');
			
			$smarty->assign('matrix', $this->_leden_voorkeuren);
			$smarty->assign('repetities', $this->_repetities);
			$smarty->display('taken/voorkeur/beheer_voorkeuren.tpl');
		}
	}
}

?>