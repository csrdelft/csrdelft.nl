<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.courantarchiefcontent.php
# -------------------------------------------------------------------
# Verzorgt het weergeven van het archief van de c.s.r.-courant
# -------------------------------------------------------------------


require_once ('class.courantcontent.php');


class CourantarchiefContent{
	
	var $courant;	

	//wat moet er gedaan worden, zie ook setZijkolom()
	var $zijkolom=false;
	
	function CourantarchiefContent(&$courant){
		$this->courant=$courant;
		//opgevraagde mail inladen
		if(isset($_GET['ID'])){
			$this->courant->load((int)$_GET['ID']);
		}
	}
	function getTitel(){
		return 'C.S.R.-courant van '.$this->getVerzendMoment();
	}
	private function getArchiefmails(){
		$aMails=$this->courant->getArchiefmails();
		$sReturn='<h1>Archief C.S.R.-courant</h1>';
		if(is_array($aMails)){
			foreach($aMails as $aMail){
				$sReturn.='<a href="/actueel/courant/archief/'.$aMail['ID'].'">'.strftime('%d %B %Y', strtotime($aMail['verzendMoment'])).'</a><br />';
			}
		}else{
			$sReturn.='Geen couranten in het archief aanwezig';
		}
		return $sReturn;
	}
	
	//tussen de modes wisselen, of een overzicht in de zijkolom, of een archiefmail in het
	//hoofdgedeelte
	function toggleZijkolom(){ $this->zijkolom=!$this->zijkolom; }
	
	function getVerzendMoment(){
		return strftime('%d %B %Y', strtotime($this->courant->getVerzendmoment()));
	}
	function view(){
		if($this->courant->getID()==0 OR $this->zijkolom){
			//overzicht
			echo $this->getArchiefmails();
		}else{
			echo '<h1>C.S.R.-courant '.$this->getVerzendMoment().'</h1>';
			echo '<iframe src="/actueel/courant/archief/iframe/'.$this->courant->getID().'"
					style="width: 100%; height: 700px; border: 0px;"></iframe>';
					
		}
	}
}//einde classe
?>
