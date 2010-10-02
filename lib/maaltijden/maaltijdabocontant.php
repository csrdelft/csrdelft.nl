<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# maaltijden/class.maaltijdcontent.php
# -------------------------------------------------------------------
# Bekijken en wijzigen van maaltijdinschrijving en abonnementen
# -------------------------------------------------------------------

require_once 'maaltijden/maaltrack.class.php';

class MaaltijdContent extends SimpleHTML {


	private $_maaltrack;

	### public ###

	function MaaltijdContent ($maaltrack) {
		$this->_maaltrack=$maaltrack;
	}
	function getTitel(){ return 'Maaltijdketzer - Overzicht abonnementen'; }

	function view(){
		//de html template in elkaar draaien en weergeven
		$profiel=new Smarty_csr();
		$profiel->caching=false;


		$profiel->assign('datumFormaat', '%a %e %b %H:%M');
		$profiel->display('maaltijdketzer/abo-overzicht.tpl');
	}
}

?>
