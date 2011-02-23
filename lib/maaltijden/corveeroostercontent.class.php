<?php

# C.S.R. Delft
# -------------------------------------------------------------------
# maaltijden/class.corveeroosterclass.php
# -------------------------------------------------------------------
# Overzicht van ingeroosterde corveeers
# -------------------------------------------------------------------


require_once 'maaltijden/maaltrack.class.php';

class CorveeroosterContent extends SimpleHTML {

	private $_maaltrack;
	private $_actie=null;

	function __construct($maaltrack) {
		$this->_maaltrack=$maaltrack;
	}
	function getTitel(){ return 'Maaltijdketzer - Corveerooster'; }

	function view(){
		$loginlid=LoginLid::instance();
		//de html template in elkaar draaien en weergeven
		$corveebeheer=new Smarty_csr();
		$corveebeheer->caching=false;

		//Alle maaltijden vanaf vorige maand tot een jaar vooruit
		$aMaal['error']=$this->_maaltrack->getError();
		$aMaal['maaltijden']=$this->_maaltrack->getMaaltijden(time()-3600*24*28, time()+3600*24*365, false, false, null, true, true);

		//arrays toewijzen en weergeven
		$corveebeheer->assign('maal', $aMaal);
		$corveebeheer->assign('datumWeek', '%W');
		$corveebeheer->assign('datumWeekdag', '%a');
		$corveebeheer->assign('datumVol', '%e %b %H:%M');
		$corveebeheer->display('maaltijdketzer/corveerooster.tpl');
	}
}

?>
