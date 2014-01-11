<?php
namespace Taken\CRV;
/**
 * CorveeRepetitiesView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van alle corvee-repetities om te beheren.
 * 
 */
class CorveeRepetitiesView extends \SimpleHtml {

	private $_repetities;
	private $_maaltijdrepetitie;
	private $_popup;
	
	public function __construct($repetities, $maaltijdrepetitie=null, $popup=null) {
		$this->_repetities = $repetities;
		$this->_maaltijdrepetitie = $maaltijdrepetitie;
		$this->_popup = $popup;
	}
	
	public function getRepetitie() {
		return $this->_repetities;
	}
	
	public function getTitel() {
		if ($this->_maaltijdrepetitie !== null) {
			return 'Corveebeheer maaltijdrepetitie: '. $this->_maaltijdrepetitie->getStandaardTitel();
		}
		return 'Beheer corveerepetities';
	}
	
	public function view() {
		$smarty = new \TemplateEngine();
		
		if ($this->_maaltijdrepetitie !== null) {
			$smarty->assign('maaltijdrepetitie', $this->_maaltijdrepetitie);
		}
		if (is_array($this->_repetities)) { // list of repetities
			$smarty->assign('popup', $this->_popup);
			$smarty->assign('melding', $this->getMelding());
			$smarty->assign('kop', $this->getTitel());
			$smarty->display('taken/menu_pagina.tpl');
			
			$smarty->assign('repetities', $this->_repetities);
			$smarty->display('taken/corvee-repetitie/beheer_corvee_repetities.tpl');
		}
		elseif (is_int($this->_repetities)) { // id of deleted repetitie
			echo '<tr id="taken-melding"><td>'. $this->getMelding() .'</td></tr>';
			echo '<tr id="repetitie-row-'. $this->_repetities .'" class="remove"></tr>';
		}
		else { // single repetitie
			echo '<tr id="taken-melding"><td>'. $this->getMelding() .'</td></tr>';
			$smarty->assign('repetitie', $this->_repetities);
			$smarty->display('taken/corvee-repetitie/beheer_corvee_repetitie_lijst.tpl');
		}
	}
}

?>