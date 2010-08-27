<?php

# C.S.R. Delft
# -------------------------------------------------------------------
# maaltijden/class.corveevoorkeurencontent.php
# -------------------------------------------------------------------
# Het weergeven van de lijst met corveevoorkeuren van alle leden
# -------------------------------------------------------------------

require_once 'maaltijden\class.corveevoorkeurenlijst.php';

class CorveevoorkeurenContent extends SimpleHTML {

	private $_sorteer;
	private $_sorteer_richting;
	private $_actie=null;

	private $_error='';

	function __construct($sorteer='uid', $sorteer_richting='asc') {
		$this->_sorteer=$sorteer;
		$this->_sorteer_richting=$sorteer_richting;
	}
	function getTitel(){ return 'Maaltijdketzer - Corveevoorkeuren'; }
	
	//Geef deze Corvee-voorkeurenlijst weer
	function view(){
		$loginlid=LoginLid::instance();
		//de html template in elkaar draaien en weergeven
		$corveevoorkeuren=new Smarty_csr();
		$corveevoorkeuren->caching=false;		
						
		//Dingen ophalen voor het overzicht van leden
		$lijst = new CorveevoorkeurenLijst();
		$aLeden = $lijst->getCorveeLedenGesorteerd($this->_sorteer, $this->_sorteer_richting);
		
		//arrays toewijzen en weergeven
		$corveevoorkeuren->assign('leden', $aLeden);
		$corveevoorkeuren->assign('sorteer', $this->_sorteer);
		$corveevoorkeuren->assign('sorteer_richting', $this->_sorteer_richting);

		$corveevoorkeuren->display('maaltijdketzer/corveevoorkeurenlijst.tpl');
	}
}

?>
