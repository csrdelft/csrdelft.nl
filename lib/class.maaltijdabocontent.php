<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.maaltijdcontent.php
# -------------------------------------------------------------------
# Bekijken en wijzigen van maaltijdinschrijving en abonnementen
# -------------------------------------------------------------------


require_once ('class.simplehtml.php');
require_once ('class.lid.php');
require_once ('class.maaltrack.php');

class MaaltijdContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;
	var $_maaltrack;

	### public ###

	function MaaltijdContent ($maaltrack) {
		$this->_lid =Lid::get_lid();
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
