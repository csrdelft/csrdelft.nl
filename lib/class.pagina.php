<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.pagina.php
# -------------------------------------------------------------------
# Weergeven en bewerken van pagina's met tekst uit de database
# -------------------------------------------------------------------

class Pagina {
	
	private $_db;
	private $_lid;
	
	private $sNaam;
	private $sTitel;
	private $sInhoud;
	
	function Pagina($sNaam) {
		$this->_lid=Lid::get_lid();
		$this->_db=MySql::get_MySql();
		
		$this->sNaam=$sNaam;
		$this->load();
	}
	
	function load() {
		$sPaginaQuery="SELECT titel, inhoud FROM pagina WHERE naam='".$this->_db->escape($this->sNaam)."'";
		$rPagina=$this->_db->query($sPaginaQuery);
		if($this->_db->numRows($rPagina)>0){
			$aPagina=$this->_db->next($rPagina);
			$this->setTitel($aPagina['titel']);
			$this->setInhoud($aPagina['inhoud']);
		}else{
			$this->setTitel('');
			$this->setInhoud('');			
		}
	}
	
	function save(){
		$sPaginaQuery = "UPDATE pagina SET titel='".$this->_db->escape($this->getTitel())."', inhoud='".$this->_db->escape($this->getInhoud())."' WHERE naam = '".$this->_db->escape($this->getNaam())."'";
		$this->_db->query($sPaginaQuery);
		if($this->_db->affected_rows()==0){
			$sPaginaQuery = "INSERT INTO pagina (naam, titel, inhoud) VALUES ('".$this->_db->escape($this->getNaam())."', '".$this->_db->escape($this->getTitel())."', '".$this->_db->escape($this->getInhoud())."')";
			$rPagina = $this->_db->query($sPaginaQuery);
		}
	}
	
	function magBewerken(){
		return $this->_lid->hasPermission('P_ADMIN');
	}
	
	function setTitel($sTitel){
		$this->sTitel=$sTitel;
	}
	
	function setInhoud($sInhoud){
		$this->sInhoud=$sInhoud;
	}
	
	function getNaam(){
		return $this->sNaam;
	}
	
	function getTitel(){
		return $this->sTitel;
	}
	
	function getInhoud(){
		return $this->sInhoud;
	}
}
?>