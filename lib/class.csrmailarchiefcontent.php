<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.csrmailcomposecontent.php
# -------------------------------------------------------------------
# Verzorgt het componeren van de mail
# -------------------------------------------------------------------


require_once ('class.mysql.php');

class Csrmailarchiefcontent extends Csrmailcontent{
	
	//csrmail-object
	var $_csrmail;	
	//id en array van de huidige csrmail
	var $iCsrmail=0;
	var $aCsrmail;	
	//wat moet er gedaan worden, zie ook setZijkolom()
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
	
	//tussen de modes wisselen, of een overzicht in de zijkolom, of een archiefmail in het
	//hoofdgedeelte
	function setZijkolom(){ $this->zijkolom=true; }
	
	function view(){
		if($this->getID()==0 OR $this->zijkolom){
			//overzicht
			echo $this->_getArchiefmails();
		}else{
			//een mail
			if(isset($_GET['iframe'])){
				echo $this->_getBody($this->getID());
			}else{
				echo '<h2>Archief C.S.R.-courant</h2>';
				echo '<iframe src="/intern/csrmail/archief/'.$this->getID().'/iframe" 
					style="width: 100%; height: 700px; border: 0px;"></iframe>';
			}		
		}
	}
}//einde classe
?>
