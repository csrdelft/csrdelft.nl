<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.maaltijdvoorkeurcontent.php
# -------------------------------------------------------------------
# Bekijken en wijzigen van voorkeuren voor maaltijdinschrijving 
# en abonnementen
# -------------------------------------------------------------------


require_once ('class.maaltrack.php');

class MaaltijdVoorkeurContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_maaltrack;

	### public ###

	function MaaltijdVoorkeurContent ($maaltrack) {
		$this->_maaltrack=$maaltrack;
	}
	function getTitel(){ return 'Maaltijdketzer - Voorkeuren'; }
	function viewWaarBenik(){ echo '<a href="/actueel/maaltijden/">Maaltijden</a> &raquo; Voorkeuren'; }
	function view(){
		$lid=Lid::get_lid();
		
		//de html template in elkaar draaien en weergeven
		$profiel=new Smarty_csr();
		$profiel->caching=false;
		
		//Dingen ophalen voor....
		//...de abonnementen
		$aMaal['abo']['abos']=$this->_maaltrack->getAbo();
		$aMaal['abo']['nietAbos']=$this->_maaltrack->getNotAboSoort();
		
		//...de eetwens
		$aMaal['eetwens']=$lid->getEetwens();
		
		//...de corveewens
		$aMaal['corveewens']=$lid->getCorveewens();
		
		
		//arrays toewijzen en weergeven
		$profiel->assign('maal', $aMaal);
		$profiel->display('maaltijdketzer/voorkeuren.tpl');
	}
}

?>
