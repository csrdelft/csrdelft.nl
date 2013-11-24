<?php
namespace Taken\MLT;
/**
 * MaaltijdRepetitiesView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle maaltijd-repetities om te beheren.
 * 
 */
class MaaltijdRepetitiesView extends \SimpleHtml {

	private $_repetities;
	private $_popup;
	
	public function __construct($repetities, $popup=null) {
		$this->_repetities = $repetities;
		$this->_popup = $popup;
	}
	
	public function getRepetitie() {
		return $this->_repetities;
	}
	
	public function getTitel() {
		return 'Beheer maaltijdrepetities';
	}
	
	public function view() {
		$smarty = new \Smarty_csr();
		
		if (is_array($this->_repetities)) { // list of repetities
			$smarty->assign('popup', $this->_popup);
			$smarty->assign('melding', $this->getMelding());
			$smarty->assign('kop', $this->getTitel());
			$smarty->display('taken/taken_menu.tpl');
			
			$smarty->assign('repetities', $this->_repetities);
			$smarty->display('taken/maaltijd-repetitie/beheer_maaltijd_repetities.tpl');
		}
		elseif (is_int($this->_repetities)) { // id of deleted repetitie
			echo '<tr id="taken-melding"><td>'. $this->getMelding() .'</td></tr>';
			echo '<tr id="repetitie-row-'. $this->_repetities .'" class="remove"></tr>';
		}
		else { // single repetitie
			echo '<tr id="taken-melding"><td>'. $this->getMelding() .'</td></tr>';
			$smarty->assign('repetitie', $this->_repetities);
			$smarty->display('taken/maaltijd-repetitie/beheer_maaltijd_repetitie_lijst.tpl');
		}
	}
}

?>