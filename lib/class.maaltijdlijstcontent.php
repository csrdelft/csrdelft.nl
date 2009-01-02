<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.maaltijdlijstpage.php
# -------------------------------------------------------------------
# Weergeven van de te printen maaltijdlijst voor een bepaalde
# maaltijd.
# -------------------------------------------------------------------


class MaaltijdLijstContent extends SimpleHTML {
	### private ###
	private $_fiscaal=false;

	# de objecten die data leveren
	var $_lid;
	var $_maaltijd;

	### public ###

	function MaaltijdLijstContent($maaltijd) {
		$this->_lid=Lid::get_lid();
		$this->_maaltijd=$maaltijd;
	}

	function setFiscaal($fiscaal){
		$this->_fiscaal=$fiscaal;
	}

	function view(){
		$maaltijdprijs=3.00; 	//maaltijdprijs voor de leden.
		$maaltijdbudget=2.00; 	//kookbudget voor de koks
		$marge=6;				//marge voor gasten.

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

		if(!$this->_fiscaal){
			//een zootje lege cellen aan het einde van de aanmeldingen array erbij maken
			$cellen=ceil($marge+($aMaal['aantal']*0.1));
			//zorgen dat er altijd een even aantal cellen is
			if(($cellen%2)!=0){ $cellen++; }

			for($i=0;$i<$cellen; $i++){
				$aMaal['aanmeldingen'][]=array('naam' => '', 'eetwens' => '');
			}
		}

		$aMaal['prijs']=$maaltijdprijs;
		//budget bepalen.
		$aMaal['budget']=($aMaal['aantal']+$marge)*$maaltijdbudget;

		$maaltijdlijst->assign('maaltijd', $aMaal);
		$maaltijdlijst->assign('datumFormaat', '%A %e %B');
		if($this->_fiscaal){
			$maaltijdlijst->display('maaltijdketzer/lijst_fiscaal.tpl');
		}else{
			$maaltijdlijst->display('maaltijdketzer/lijst.tpl');
		}

	}
}

?>
