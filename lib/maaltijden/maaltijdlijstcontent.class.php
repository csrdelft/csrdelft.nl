<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# maaltijden/class.maaltijdlijstpage.php
# -------------------------------------------------------------------
# Weergeven van de te printen maaltijdlijst voor een bepaalde
# maaltijd.
# -------------------------------------------------------------------


class MaaltijdLijstContent extends SimpleHTML {

	private $_fiscaal=false;
	private $_maaltijd;

	function __construct($maaltijd) {
		$this->_maaltijd=$maaltijd;
	}

	function setFiscaal($fiscaal){
		$this->_fiscaal=$fiscaal;
	}

	function view(){
		$loginlid=LoginLid::instance();
		$maaltijdprijs=3.00; 	//maaltijdprijs voor de leden.
		$maaltijdbudget=2.00; 	//kookbudget voor de koks
		
		//de html template in elkaar draaien en weergeven
		$maaltijdlijst=new Smarty_csr();
		$maaltijdlijst->caching=false;
		
		$aMaal['id']=$this->_maaltijd->getMaalId();
		$aMaal['datum']=$this->_maaltijd->getDatum();
		$aMaal['gesloten']=$this->_maaltijd->isGesloten();
		$aMaal['magSluiten']=($loginlid->hasPermission('P_MAAL_MOD') OR opConfide());
		$aMaal['taken']=$this->_maaltijd->getTaken();
		$aMaal['koks']=$this->_maaltijd->getKoks();
		$aMaal['afwassers']=$this->_maaltijd->getAfwassers();
		$aMaal['theedoeken']=$this->_maaltijd->getTheedoeken();
		
		$tp=LidCache::getLid($this->_maaltijd->getTP());

		$aMaal['tafelpraeses']=$tp->getNaamLink('civitas', 'html');

		$aMaal['aanmeldingen']=$this->_maaltijd->getAanmeldingen_Oud();
		$aMaal['aantal']=count($aMaal['aanmeldingen']);

		//kleinere marge bij minder mensen.
		if($aMaal['aantal']<=39){
			$marge=3;
		}elseif($aMaal['aantal']<=49){
			$marge=4;
		}elseif($aMaal['aantal']<=59){
			$marge=5;
		}else{
			$marge=6;
		}
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

		if($this->_fiscaal){
			$maaltijdlijst->assign('datumFormaat', '%F %H:%M');
			$maaltijdlijst->display('maaltijdketzer/lijst_fiscaal.tpl');
		}else{
			$maaltijdlijst->assign('datumFormaat', '%A %e %B');
			$maaltijdlijst->display('maaltijdketzer/lijst.tpl');
		}

	}
}

?>
