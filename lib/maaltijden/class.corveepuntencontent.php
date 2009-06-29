<?php

# C.S.R. Delft
# -------------------------------------------------------------------
# maaltijden/class.corveebeheercontent.php
# -------------------------------------------------------------------
# Toevoegen en bewerken van maaltijden
# -------------------------------------------------------------------


require_once ('maaltijden/class.maaltrack.php');

class CorveepuntenContent extends SimpleHTML {

	private $_maaltrack;
	private $_actie=null;

	private $_error='';

	function __construct($maaltrack) {
		$this->_maaltrack=$maaltrack;
	}
	function getTitel(){ return 'Maaltijdketzer - Corveepunten'; }
	
	//functie om een lid in het formulier te laden
	/*function load($iLidID, $actie = null){
		$iLidID=(int)$iLidID;
		$this->_actie=$actie;
		$this->_maaltijd=$this->_maaltrack->getMaaltijd($iMaalID);
	}*/

	function view(){
		$loginlid=LoginLid::instance();
		//de html template in elkaar draaien en weergeven
		$corveepunten=new Smarty_csr();
		$corveepunten->caching=false;

		//Dingen ophalen voor het overzicht van leden
		$aLeden=$this->_maaltrack->getPuntenlijst();
		
		if ($this->_actie == 'bekijk')
			$aLeden['actie']='bekijk';

		//arrays toewijzen en weergeven
		$corveepunten->assign('leden', $aLeden);
		$corveepunten->display('maaltijdketzer/corveepunten.tpl');
	}
}

?>
