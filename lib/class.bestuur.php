<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.bestuur.php
# -------------------------------------------------------------------


require_once('class.simplehtml.php');
class Bestuur extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_db;
	var $_lid;

	var $_jaar='';
	var $_aBestuur='';
	function Bestuur (&$lid, &$db) {
		$this->_lid =& $lid;
		$this->_db =& $db;
	}
	
	function loadBestuur($jaar=0){
		//leeggooien
		$this->_jaar=0;
		$this->_aBestuur=array();
		
		$jaar=(int)$jaar;
		if($jaar==0){
			//huidige bestuur laden...
			$jaar=date('Y');
			if(date('m-d')<'06-16') $jaar-=1;
		}else{
			if(!preg_match('/\d{4}/', $jaar)) return false;
			$this->_jaar=$jaar;
		}
		$sBestuur="
			SELECT
				ID, jaar, naam, 
				praeses, abactis, fiscus, vice_praeses, vice_abactis, 
				verhaal, bbcode_uid, tekst
			FROM
				bestuur
			WHERE
				jaar=".$jaar."
			LIMIT 1;";

		$rBestuur=$this->_db->query($sBestuur);
		if($rBestuur===false){ 
			return $this->loadBestuur($jaar-1); 
		}else{
			while($bestuursLid=$this->_db->next($rBestuur)){
				$this->_aBestuur=array(
					'ID' => $bestuursLid['ID'], 'jaar' => $bestuursLid['jaar'], 'naam' => $bestuursLid['naam'],
					'praeses' => $this->_lid->getNaamLink($bestuursLid['praeses']),
					'abactis' => $this->_lid->getNaamLink($bestuursLid['abactis']),
					'fiscus' => $this->_lid->getNaamLink($bestuursLid['fiscus']),
					'vice_praeses' => $this->_lid->getNaamLink($bestuursLid['vice_praeses']),
					'vice_abactis' => $this->_lid->getNaamLink($bestuursLid['vice_abactis']),
					'verhaal' => $bestuursLid['verhaal'],
					'bbcode_uid' => $bestuursLid['bbcode_uid'],
					'tekst' => $bestuursLid['tekst']);
			}
			return true;
		}
	}
	
	function isBestuur(){ return in_array($this->_lid->getUid(), $this->_aBestuur); }
	function getBestuur(){ 
		if(!isset($this->_aBestuur['naam'])){
			//kennelijk nog niets geladen, dan nu maar doen.
			$this->loadBestuur();	
		}
		return $this->_aBestuur;	
	}

	function getBesturen(){
		$sBesturen="
			SELECT 
				jaar, naam, praeses
			FROM
				bestuur
			ORDER BY
				jaar DESC";
		$rBesturen=$this->_db->query($sBesturen);
		if($rBesturen!==false AND $this->_db->numRows($rBesturen)!=0){
			return $this->_db->result2array($rBesturen);
		}else{
			return false;
		}
	}	
	
}

?>
