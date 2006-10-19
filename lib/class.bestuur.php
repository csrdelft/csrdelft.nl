<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.bestuur.php
# -------------------------------------------------------------------



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
		$this->loadBestuur();
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
				ID, startjaar, naam, 
				praeses, abictis, fiscus, vice-praeses, vice-abactis, 
				tekst, bbcode_uid
			FROM
				bestuur
			WHERE
				startjaar=".$jaar."
			LIMIT 1;";
		$rBestuur=$this->_db->query($sBestuur);
		if($rBestuur===false){ 
			$this->_loadBestuur($jaar-1); 
		}else{
			$this->_aBestuur=$this->_db->result2array($rBestuur);
		}
	}
	
	function isBestuur(){ return in_array($this->_lid->getUid(), $this->_aBestuur) }
	function getBestuur(){ return $this->_aBestuur;	}
	
}

?>
