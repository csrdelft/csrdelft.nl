<?php
#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.agenda.php
# -------------------------------------------------------------------
# Verzorgt het opvragen van agenda
# -------------------------------------------------------------------
# Historie:
# 07-10-2005 Jieter
# . gemaakt
#
require_once ('class.mysql.php');

class Agenda{

	var $_lid;
	var $_db;
	var $_agenda= array(); 		//de uiteindelijke agenda-array
	var $_aKalenders=array(); 	//de kalenders die per geladen moeten worden
	
	function Agenda(&$lid){
		$this->_lid=& $lid;
		$this->_db = new MySQL ();
		$this->_db->connect();
	}
	
	//activiteiten inladen. Vanaf vandaag tot 
	function loadAgenda($eindDatum){
		
		
		
		$this->_agenda=$aActiviteiten;
	}
	//zet de kalenders die geladen moeten worden..
	//dit kan uit de database gehaald worden of meegegeven worden aan de methode
	function setKalenders($Kalenders=false){
		if($Kalenders===false){
			//ophalen uit het databeest
			
			$aKalender=array();
		}else{
			//array gebruiken die meegegeven is. Wel eerst controleren of dat een valide array is
			//anders alles gebruiken
			$bValide=true;
			foreach($aKelenders as $sKalender){
				//controleren...
				
			}
			if($bValide===true){
				//opgegeven in methodeAanroep
				$aKalenders=$aKalenders;
			}else{
				$aKalender=$this->getAllKalenders();//alles gebruiken
			}

		}
		return $aKalenders;
	}
	
	//array met agenda activiteiten teruggeven
	function getAgenda(){
		return $this->_agenda;
	}
	
	function getAllKalenders(){
		$sAlleKalendersQuery="
			SELECT
				kid, bestandsnaam, naam
			FROM
				agendakalenders;";
		$rAlleKalendersResultaat=$this->_db->select($sAlleKalendersQuery);
		
		while($aAlleKalenders=$this->_db->next($rAlleKalendersResultaat)){
			$aAlleKalendersReturn[]=$aAlleKalenders;
		}
		return $aAlleKalendersReturn;
	}
	
	function getParsedKalender($sFilename){
		$sFilename='/srv/www/www.csrdelft.nl/lib/agenda/kalenders/'.$sFilename;
		if(file_exists($sFilename)){
			
			include_once('import_ical.php');
			return parse_ical($sFilename);
		}else{
			return false;
		}
		
	}
	
	//zet kalenders voor gebruiker
	function setKalendersVoorLid($aKalenders){
	
	}
		
	
}
?>