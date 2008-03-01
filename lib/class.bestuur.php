<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.bestuur.php
# -------------------------------------------------------------------

class Bestuur{

	private $_lid;

	private $_jaar='';
	private $_aBestuur='';
	
	function Bestuur() {
		$this->_lid=Lid::get_lid();
	}
	
	//kijk of er een bestuur ingeladen is, anders het huidige inladen.
	function loadIfNot(){
		if(!isset($this->_aBestuur['naam'])){
			//kennelijk nog niets geladen, dan nu maar doen.
			$this->loadBestuur();	
		}
	}
	
	function loadBestuur($jaar=0){
		$db=MySql::get_MySql();
		//leeggooien
		$this->_jaar=0;
		$this->_aBestuur=array();
		
		$jaar=(int)$jaar;
		if($jaar==0){
			//huidige bestuur laden...
			$jaar=date('Y');
			if(date('m-d')<'06-28') $jaar-=1;
		}else{
			if(!preg_match('/(19|20)\d{2}/', $jaar)) return false;
			$this->_jaar=$jaar;
		}
		$sBestuur="
			SELECT
				ID, jaar, naam, 
				praeses, abactis, fiscus, vice_praeses, vice_abactis, 
				verhaal, tekst
			FROM
				bestuur
			WHERE
				jaar=".$jaar."
			LIMIT 1;";

		$rBestuur=$db->query($sBestuur);
		if($rBestuur===false){ 
			return $this->loadBestuur($jaar-1); 
		}else{
			$this->_aBestuur=$db->next($rBestuur);
			$this->_aBestuur['isAdmin']=$this->_lid->hasPermission('P_ADMIN') OR $this->isBestuur();
			return true;
		}
	}
	public function save(){
		$db=MySql::get_MySql();
		$bestuurUpdate="
			UPDATE bestuur 
			SET 
				verhaal='".$db->escape($this->_aBestuur['verhaal'])."', 
				tekst='".$db->escape($this->_aBestuur['tekst'])."'
			WHERE ID=".$this->_aBestuur['ID']."
			LIMIT 1;";
		return $db->query($bestuurUpdate);
	}
	
	//check of de huidige gebruiker of $uid in het bestuur zit.
	public function isBestuur($uid=''){
		$this->loadIfNot();
		if($uid==''){ $uid=$this->_lid->getUid(); }
		return in_array($uid, $this->_aBestuur); 
	}
	public function getBestuur(){ 
		$this->loadIfNot();
		return $this->_aBestuur;	
	}
	public function getJaar(){
		$this->loadIfNot();
		return $this->_aBestuur['jaar'];
	}
	public function setVerhaal($verhaal){
		$this->_aBestuur['verhaal']=$verhaal;
	}
	public function setTekst($tekst){
		$this->_aBestuur['tekst']=$tekst;
	}

	
	//regel een lijst met besturen die zich in de database bevinden
	public static function getBesturen(){
		$db=MySql::get_MySql();
		$sBesturen="
			SELECT 
				jaar, naam, praeses
			FROM
				bestuur
			ORDER BY
				jaar DESC";
		$rBesturen=$db->query($sBesturen);
		if($rBesturen!==false AND $db->numRows($rBesturen)!=0){
			return $db->result2array($rBesturen);
		}else{
			return false;
		}
	}	
	
}

?>
