<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.maaltijdbeheercontent.php
# -------------------------------------------------------------------
#
# Toevoegen en bewerken van maaltijden
#
# -------------------------------------------------------------------
# Historie:
# 20-01-2006 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('class.lid.php');
require_once ('class.maaltrack.php');

class MaaltijdbeheerContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_lid;
	var $_maaltrack;

	var $_maaltijd=null;
	
	var $_error='';
	### public ###

	function MaaltijdbeheerContent (&$lid, &$maaltrack) {
		$this->_lid =& $lid;
		$this->_maaltrack =& $maaltrack;
	}
	function getTitel(){ return 'Maaltijdketzer - beheer'; }
	
	//functie om een maaltijd in het formulier te laden, normaal gewoon een formulier voor nieuwe maaltijden.
	function load($iMaalID){
		$iMaalID=(int)$iMaalID;
		$this->_maaltijd=$this->_maaltrack->getMaaltijd($iMaalID);
	}
	function addError($error){ $this->_error=$error; }
	
	function view(){
		
		//de html template in elkaar draaien en weergeven
		$maaltijdbeheer=new Smarty_csr();
		$maaltijdbeheer->caching=false;
		
		//Dingen ophalen voor het overzicht van maaltijden...
		$aMaal['error']=$this->_maaltrack->getError();
		$aMaal['maaltijden']=$this->_maaltrack->getMaaltijden(time(), time()+3600*24*100, false);
		
		
		//nieuwe maaltijd, of oude bewerken?
		if($this->_maaltijd==null OR !is_array($this->_maaltijd)){
			//nieuwe maaltijd, standaardwaarden
			$aForm['id']=0;
			$aForm['actie']='toevoegen';
			$aForm['moment']=time();
			$aForm['abo']='';
			$aForm['max']=100;
			//alles standaard naar jan lid.
			$aForm['tp_uid']=$aForm['kok1_uid']=$aForm['kok2_uid']=
				$aForm['afw1_uid']=$aForm['afw2_uid']=$aForm['afw3_uid']='x101';
		}else{
			$aForm=$this->_maaltijd;
			$aForm['actie']='bewerken';
		}
		# als er een error gegeven wordt, is er hoogstwaarschijnlijk wat mis gegaan bij het bewerken of toevoegen
		# van een nieuwe maaltijd. Daarom kijken we hier of er nog zinnige invoer uit de post te halen valt.
		if($this->_error!=''){
			if(isset($_POST['moment'])){ $aForm['moment']=strtotime($_POST['moment']); }
			if(isset($_POST['omschrijving'])){ $aForm['tekst']=trim(mb_htmlentities($_POST['omschrijving'])); }
			if(isset($_POST['limiet']) AND $_POST['limiet']==(int)$_POST['limiet']){ $aForm['max']=$_POST['limiet']; }
			if(isset($_POST['abo']) AND $this->_maaltrack->isValidAbo($_POST['abo'])){ $aForm['abo']=$_POST['abo']; }
			if(isset($_POST['tp']) AND $this->_lid->uidExists($_POST['tp']) ){ $aForm['tp_uid']=$_POST['tp']; }
		}	
		$aForm['abos']=$this->_maaltrack->getAbos();
		$aMaal['formulier']=$aForm;

		//arrays toewijzen en weergeven
		$maaltijdbeheer->assign('maal', $aMaal);
		$maaltijdbeheer->assign('toonLijsten', $this->_lid->hasPermission('P_MAAL_MOD') or opConfide());
		$maaltijdbeheer->assign('datumFormaat', '%a %e %b %H:%M');
		$maaltijdbeheer->assign('datumFormaatInvoer', '%Y-%m-%d %H:%M'); 
		if($this->_error!=''){ $maaltijdbeheer->assign('error', $this->_error); }
		$maaltijdbeheer->display('maaltijdbeheer.tpl');
	}
}

?>
