<?php

# C.S.R. Delft
# -------------------------------------------------------------------
# maaltijden/class.corveebeheercontent.php
# -------------------------------------------------------------------
# Toevoegen en bewerken van maaltijden
# -------------------------------------------------------------------


require_once 'maaltijden/maaltrack.class.php';

class CorveepuntenContent extends SimpleHTML {

	private $_maaltrack;
	private $_actie=null;

	private $_error='';

	function __construct($maaltrack) {
		$this->_maaltrack=$maaltrack;
	}
	function getTitel(){ return 'Maaltijdketzer - Corveepunten'; }
	
	function view(){
		$loginlid=LoginLid::instance();
		//de html template in elkaar draaien en weergeven
		$corveepunten=new Smarty_csr();
		$corveepunten->caching=false;
		
		$sorteer = 'achternaam';
		$sorteer_richting = 'asc';
		if (isset($_GET['sorteer'])) $sorteer = $_GET['sorteer'];
		elseif (isset($_POST['sorteer'])) $sorteer = $_POST['sorteer'];
		if (isset($_GET['sorteer_richting'])) $sorteer_richting = $_GET['sorteer_richting'];
		elseif (isset($_POST['sorteer_richting'])) $sorteer_richting = $_POST['sorteer_richting'];
		
		//Dingen ophalen voor het overzicht van leden
		$aLeden=$this->_maaltrack->getPuntenlijst($sorteer, $sorteer_richting);
		
		$bewerkt_lid = (isset($_POST['uid']) ? $_POST['uid'] : '');

		//arrays toewijzen en weergeven
		$corveepunten->assign('leden', $aLeden);
		$corveepunten->assign('sorteer', $sorteer);
		$corveepunten->assign('sorteer_richting', $sorteer_richting);
		$corveepunten->assign('bewerkt_lid', $bewerkt_lid);

		$corveepunten->display('maaltijdketzer/corveepunten.tpl');
	}
}

?>
