<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.pagina.php
# -------------------------------------------------------------------
# Weergeven en bewerken van pagina's met tekst uit de database
# -------------------------------------------------------------------

class Pagina{

	private $_db;
	private $_lid;

	private $sNaam;
	private $sTitel;
	private $sInhoud;
	private $sRechtenBekijken='P_NOBODY';
	private $sRechtenBewerken='P_ADMIN';

	function Pagina($sNaam){
		$this->_lid=Lid::instance();
		$this->_db=MySql::instance();

		$this->sNaam=$sNaam;
		$this->load();
	}
	
	function getPaginas(){
		$sPaginasQuery="SELECT naam, titel, rechten_bewerken FROM pagina ORDER BY titel ASC";
		$rPaginas=$this->_db->query($sPaginasQuery);
		$aPaginas=array();
		while($aPagina=$this->_db->next($rPaginas)){
			if($this->_lid->hasPermission($aPagina['rechten_bewerken'])){
				$aPaginas[]=$aPagina;
			}			
		}
		return $aPaginas;
	}

	function load(){
		$sPaginaQuery="SELECT titel, inhoud, rechten_bekijken, rechten_bewerken FROM pagina WHERE naam='".$this->_db->escape($this->sNaam)."'";
		$rPagina=$this->_db->query($sPaginaQuery);
		if($this->_db->numRows($rPagina)>0){
			$aPagina=$this->_db->next($rPagina);
			$this->setTitel($aPagina['titel']);
			$this->setInhoud($aPagina['inhoud']);
			$this->sRechtenBekijken=$aPagina['rechten_bekijken'];
			$this->sRechtenBewerken=$aPagina['rechten_bewerken'];
		}else{
			$this->setTitel('');
			$this->setInhoud('');
			$this->sRechtenBekijken='P_NOBODY';
			$this->sRechtenBewerken='P_ADMIN';
		}
	}

	function save(){
		$sPaginaQuery = "UPDATE pagina SET titel='".$this->_db->escape($this->getTitel())."', inhoud='".$this->_db->escape($this->getInhoud())."', rechten_bekijken='".$this->_db->escape($this->sRechtenBekijken)."', rechten_bewerken='".$this->_db->escape($this->sRechtenBewerken)."' WHERE naam = '".$this->_db->escape($this->getNaam())."'";
		$this->_db->query($sPaginaQuery);
		if($this->_db->affected_rows()==0){
			$sPaginaQuery = "INSERT INTO pagina (naam, titel, inhoud, rechten_bekijken, rechten_bewerken) VALUES ('".$this->_db->escape($this->getNaam())."', '".$this->_db->escape($this->getTitel())."', '".$this->_db->escape($this->getInhoud())."', '".$this->_db->escape($this->sRechtenBekijken)."', '".$this->_db->escape($this->sRechtenBewerken)."')";
			$rPagina = $this->_db->query($sPaginaQuery);
		}
	}

	function setRechtenBekijken($sRechten){
		$this->sRechtenBekijken=$sRechten;
	}
	function getRechtenBekijken(){
		return $this->sRechtenBekijken;
	}
	function magBekijken(){
		return $this->_lid->hasPermission($this->sRechtenBekijken);
	}
	function setRechtenBewerken($sRechten){
		$this->sRechtenBewerken=$sRechten;
	}
	function getRechtenBewerken(){
		return $this->sRechtenBewerken;
	}
	function magBewerken(){
		return $this->_lid->hasPermission($this->sRechtenBewerken);
	}
	function magPermissiesBewerken(){
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