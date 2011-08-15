<?php

# C.S.R. Delft
# -------------------------------------------------------------------
# maaltijden/class.corveeroosterclass.php
# -------------------------------------------------------------------
# Overzicht van ingeroosterde corveeers
# -------------------------------------------------------------------


require_once 'maaltijden/maaltrack.class.php';
require_once 'maaltijden/corveeinstellingen.class.php';

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


		$aMaal['error']=$this->_maaltrack->getError();
		// Voor leden: Alle maaltijden vanaf vorige maand tot einddatum corveeinstellingen.
		// Voor MaalCie: het tijdvak zoals ingesteld bij corveeinstellingen.
		if($loginlid->hasPermission('P_MAAL_MOD')){
			$roosterbegin = strtotime(Corveeinstellingen::get('roosterbegin'));
		}else{
			$roosterbegin = time()-3600*24*28;
		}
		$roostereind = strtotime(Corveeinstellingen::get('roostereind'));
		$aMaal['maaltijden']=$this->_maaltrack->getMaaltijden($roosterbegin, $roostereind, false, false, null, true, true);

		//arrays toewijzen en weergeven
		$corveebeheer->assign('maal', $aMaal);
		$corveebeheer->assign('liduid', $loginlid->getUid());
		$corveebeheer->assign('datumWeek', '%W');
		$corveebeheer->assign('datumWeekdag', '%a');
		$corveebeheer->assign('datumVol', '%e %b %H:%M');
		$corveebeheer->display('maaltijdketzer/corveerooster.tpl');
	}
}

?>
