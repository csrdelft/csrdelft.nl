<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.maaltijdlijstpage.php
# -------------------------------------------------------------------
# Weergeven van de te printen maaltijdlijst voor een bepaalde
# maaltijd.
# -------------------------------------------------------------------


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
		$cellen=ceil($marge+($aMaal['aantal']*0.1));
		//zorgen dat er altijd een even aantal cellen is
		if(($cellen%2)!=0){ $cellen++; }
		
		
		for($i=0;$i<$cellen; $i++){
			$aMaal['aanmeldingen'][]=array('naam' => '', 'eetwens' => '');
		}
		
		//budget bepalen.
		$aMaal['budget']=($aMaal['aantal']+$marge)*$maaltijdbudget;
			
		$maaltijdlijst->assign('maaltijd', $aMaal);
		$maaltijdlijst->assign('datumFormaat', '%A %e %B'); 
		$maaltijdlijst->display('maaltijdlijst.tpl');
		
	}
}

?>
