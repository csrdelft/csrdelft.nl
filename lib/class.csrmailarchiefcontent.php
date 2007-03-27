<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.csrmailcomposecontent.php
# -------------------------------------------------------------------
# Verzorgt het componeren van de mail
# -------------------------------------------------------------------
# Historie:
# 01-10-2005 Jieter
# . gemaakt
#

require_once ('class.mysql.php');

class Csrmailarchiefcontent extends Csrmailcontent{
	
	var $_csrmail;
	
	var $iCsrmail=0;
	var $aCsrmail;
	
	var $zijkolom=false;
	
	function Csrmailarchiefcontent(&$csrmail){
		$this->Csrmailcontent($csrmail);
		if(isset($_GET['ID'])){
			$this->iCsrmail=(int)$_GET['ID'];
		}
	}
	function getID(){
		return $this->iCsrmail;
	}
	function setZijkolom(){ $this->zijkolom=true; }

	
	
	//function lees($
	function view(){
		if($this->getID()==0 OR $this->zijkolom){
			//overzicht
			echo $this->_getArchiefmails();
		}else{
			//een mail
			if(isset($_GET['iframe'])){
				echo $this->_getBody($this->getID());
			}else{
				echo '<h2>PubCie-Courant archief</h2>';
				echo '<iframe src="/intern/csrmail/archief/'.$this->getID().'/iframe" 
					style="width: 100%; height: 700px; border: 0px;"></iframe>';
			}		
		}
	}
}//einde classe
?>
