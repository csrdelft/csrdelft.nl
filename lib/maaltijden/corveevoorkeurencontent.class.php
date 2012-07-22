<?php

# C.S.R. Delft
# -------------------------------------------------------------------
# maaltijden/class.corveevoorkeurencontent.php
# -------------------------------------------------------------------
# Het weergeven van de lijst met corveevoorkeuren van alle leden
# -------------------------------------------------------------------

require_once 'maaltijden/corveevoorkeurenlijst.class.php';

class CorveevoorkeurenContent extends SimpleHTML {

	private $_sorteer;
	private $_sorteer_richting;
	private $_actie=null;

	private $_error='';

	function __construct($sorteer='achternaam', $sorteer_richting='asc') {
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
		//eventuele fouten ophalen
		$this->setMelding($lijst->getError());

		//arrays toewijzen en weergeven
		$corveevoorkeuren->assign('voorkeurenheaders', array('Kl Li','Kl Zw','Wo Kok','Wo Afw','Do Kok','Do Afw','Theedk','Sc Afz','Sc Fri','Sc Keu'));
		$corveevoorkeuren->assign('leden', $aLeden);
		$corveevoorkeuren->assign('sorteer', $this->_sorteer);
		$corveevoorkeuren->assign('sorteer_richting', $this->_sorteer_richting);
		$corveevoorkeuren->assign('melding', $this->getMelding());

		$corveevoorkeuren->display('maaltijdketzer/corveevoorkeurenlijst.tpl');
	}
}

?>
