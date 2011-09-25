<?php

# C.S.R. Delft
# -------------------------------------------------------------------
# maaltijden/maalabobeheercontent.class.php
# -------------------------------------------------------------------
# Toevoegen en bewerken van abo's
# -------------------------------------------------------------------


require_once 'maaltijden/maaltrack.class.php';

class MaaltijdabobeheerContent extends SimpleHTML {

	private $_maaltrack;
	private $_actie=null;

	private $_error='';

	function __construct($maaltrack) {
		$this->_maaltrack=$maaltrack;
	}
	function getTitel(){ return 'Maaltijdketzer - Abonnementen'; }
	
	function view(){
		//de html template in elkaar draaien en weergeven
		$maalabos=new Smarty_csr();
		$maalabos->caching=false;

		//Dingen ophalen voor het overzicht van leden
		$aLeden=$this->_maaltrack->getLedenAbo();

		//arrays toewijzen en weergeven
		$maalabos->assign('leden', $aLeden);
		$maalabos->display('maaltijdketzer/abobeheer.tpl');
	}
}

?>
