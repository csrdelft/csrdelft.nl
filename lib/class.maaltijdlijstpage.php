<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.maaltijdlijstpage.php
# -------------------------------------------------------------------
#
# Weergeven van de te printen maaltijdlijst voor een bepaalde
# maaltijd.
#
# -------------------------------------------------------------------
# Historie:
# 26-02-2006 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('class.lid.php');
require_once ('class.maaltrack.php');

class MaaltijdLijstPage extends SimpleHTML {
	### private ###

	# de objecten die data leveren
	var $_lid;
	var $_maaltijd;

	### public ###

	function MaaltijdLijstPage (&$lid, &$maaltijd) {
		$this->_lid =& $lid;
		$this->_maaltijd =& $maaltijd;
	}

	function view(){
		$maaltijdbudget=1.70;
		$marge=6;
		
		//de html template in elkaar draaien en weergeven
		$maaltijdlijst=new Smarty_csr();
		$maaltijdlijst->caching=false;
		
		$aMaal['id']=$this->_maaltijd->getMaalId();
		$aMaal['datum']=$this->_maaltijd->getDatum();
		$aMaal['gesloten']=$this->_maaltijd->isGesloten();
		$aMaal['tafelpraeses']=$this->_lid->getCivitasName($this->_maaltijd->getTP());
				
		$aMaal['aanmeldingen']=$this->_maaltijd->getAanmeldingen_Oud();
		$aMaal['aantal']=count($aMaal['aanmeldingen']);
		$aMaal['marge']=$marge;
		$aMaal['totaal']=$marge+$aMaal['aantal'];
		
		//een zootje lege cellen aan het einde van de aanmeldingen array erbij maken
		for($i=0;$i<($marge+($aMaal['aantal']*0.1)); $i++){
			$aMaal['aanmeldingen'][]=array('naam' => '', 'eetwens' => '');
		}
		
		//budget bepalen.
		$aMaal['budget']=($aMaal['aantal']+$marge)*$maaltijdbudget;
			
		$maaltijdlijst->assign('maaltijd', $aMaal);
		$maaltijdlijst->assign('datumFormaat', '%a %e %b %H:%M'); 
		$maaltijdlijst->display('maaltijdlijst.tpl');
		
	}
}

?>
