<?php
namespace Taken\CRV;
/**
 * MijnCorveeView.class.php	| 	P.W.G. Brussee (brussee@live.nl)
 * 
 * Tonen van de corveepunten, vrijstellingen en corveetaken van een lid.
 * 
 */
class MijnCorveeView extends \SimpleHtml {

	private $_rooster;
	private $_punten;
	private $_functies;
	private $_vrijstelling;
	
	public function __construct($taken, $punten, $functies, $vrijstelling) {
		$this->_rooster = $taken;
		$this->_punten = $punten;
		$this->_functies = $functies;
		$this->_vrijstelling = $vrijstelling;
	}
	
	public function getTitel() {
		return 'Mijn corveeoverzicht';
	}
	
	public function view() {
		$smarty= new \TemplateEngine();
		
		$smarty->assign('melding', $this->getMelding());
		$smarty->assign('kop', $this->getTitel());
		$smarty->display('taken/menu_pagina.tpl');
		
		$smarty->assign('rooster', $this->_rooster);
		$smarty->display('taken/corveetaak/mijn_rooster.tpl');
		
		$smarty->assign('puntenlijst', $this->_punten);
		$smarty->assign('functies', $this->_functies);
		$smarty->display('taken/corveepunt/mijn_punten.tpl');
		
		$smarty->assign('vrijstelling', $this->_vrijstelling);
		$smarty->display('taken/vrijstelling/mijn_vrijstelling.tpl');
	}
}

?>