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
		$sReturn='<div id="archiefCourant">
				<a class="kopje" href="/intern/courant/">Archief</a><br />';
		if(is_array($aMails)){
			foreach($aMails as $aMail){
				$sReturn.='<a href="/intern/courant/archief/'.$aMail['ID'].'">'.strftime('%d %B %Y', strtotime($aMail['verzendMoment'])).'</a><br />';
			}
		}else{
			$sReturn.='Geen couranten in het archief aanwezig';
		}
		$sReturn.='</div>';
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
			echo '<h2>C.S.R.-courant '.$this->getVerzendMoment().'</h2>';
			echo '<iframe src="/intern/courant/archief/iframe/'.$this->courant->getID().'"
					style="width: 100%; height: 700px; border: 0px;"></iframe>';
					
		}
	}
}//einde classe
?>
