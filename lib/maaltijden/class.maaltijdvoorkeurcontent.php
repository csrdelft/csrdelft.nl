<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# maaltijden/class.maaltijdvoorkeurcontent.php
# -------------------------------------------------------------------
# Bekijken en wijzigen van voorkeuren voor maaltijdinschrijving
# en abonnementen
# -------------------------------------------------------------------


require_once ('maaltijden/class.maaltrack.php');

class MaaltijdVoorkeurContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_maaltrack;

	### public ###

	function MaaltijdVoorkeurContent ($maaltrack) {
		$this->_maaltrack=$maaltrack;
	}
	function getTitel(){ return 'Maaltijdketzer - Voorkeuren'; }

	function view(){
		$loginlid=LoginLid::instance();

		//de html template in elkaar draaien en weergeven
		$profiel=new Smarty_csr();
		$profiel->caching=false;

		//Dingen ophalen voor....
		//...de abonnementen
		$aMaal['abo']['abos']=$this->_maaltrack->getAbo();
		$aMaal['abo']['nietAbos']=$this->_maaltrack->getNotAboSoort();

		//...de eetwens
		$aMaal['eetwens']=$loginlid->getLid()->getProperty('eetwens');
		
		//...de corveewens
		$aMaal['corvee_wens']=$loginlid->getLid()->getProperty('corvee_wens');
		
		//...de corveewens
		$aMaal['corvee_voorkeuren']=$loginlid->getLid()->getCorveeVoorkeuren();		

		//arrays toewijzen en weergeven
		$profiel->assign('maal', $aMaal);
		$profiel->display('maaltijdketzer/voorkeuren.tpl');
	}
}

?>
