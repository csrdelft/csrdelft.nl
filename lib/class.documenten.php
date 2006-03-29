<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.documenten.php
# -------------------------------------------------------------------
#
# -------------------------------------------------------------------
# Historie:
# 23-01-2006 Matthijs Neven
# . gemaakt
#

//require_once ('class.mysql.php');

class Documenten {
	var $_db;
	var $_lid;

	function Documenten(&$lid, &$db){
                 $this->_lid =& $lid;
		 $this->_db =& $db;
	}
	
	function getDocumenten(){
		$rDocumenten=$this->_db->select("
			SELECT
                              naam, cat, eigenaar, datum, bestandsnaam
			FROM
                              documenten
			ORDER BY
 			      cat, datum, naam"
			);
                //array met alle documenten, bevat arrays met categorien
                $aDoc=array();
                //array met categorie
                $aCat=array();
                $catNaam="";
		while($aDocData=$this->_db->next($rDocumenten)){
                        //catID omzetten in naam
                        $naam = $this->getCategorieNaam($aDocData['cat']);
                        $aDocData['cat']= $naam;
                        
                        // Nieuwe categorie
                        if($catNaam!==$aDocData['cat']){
                          $aDoc[]=$aCat;
                          $aCat=array();
                          $catNaam=$naam;
                          $aCat[]=$catNaam;
                        }
                        //document toevoegen
                        $aCat[]=$aDocData;
                 }

                //laatste categorie toevoegen
                $aDoc[]=$aCat;

		return $aDoc;
	}
	
	function getCategorien(){
		$rCategorien=$this->_db->select("
			SELECT
                              ID, naam
			FROM
                              documentencategorie
			ORDER BY
			      ID");

                //array met categorie
                $aCat=array();
                
		while($aCatData=$this->_db->next($rCategorien)){
                        $aCat[]=$aCatData;
                 }

		return $aCat;
	}
	
	function getCategorieNaam($catID){
                //array met categorie
                $aCat= $this->getCategorien();

		for($i = 0; $i<count($aCat); $i++){
                  if($aCat[$i]['ID'] == $catID)
                    return $aCat[$i]['naam'];
                 }

		return "foute naam";
	}
	
	function getCategorieID($catNaam){
                //array met categorie
                $aCat= $this->getCategorien();

		for($i = 0; $i<count($aCat); $i++){
                  if($aCat[$i]['naam'] == $catNaam)
                    return $aCat[$i]['ID'];
                 }

		return "fout ID";
	}
	
	function add($pad, $name, $catNaam){
                  $datum=date('y-m-d');
                  $uid=$this->_lid->getUid();
                  
                  //als het document al bestaat -> niet toevoegen
                  $dubbel=0;
                  $aDoc=$this->getDocumenten();
                  for($i=1;$i<count($aDoc);$i++){
                      if($catNaam == $aDoc[$i][0])
                          $dubbel++;
                      for($j=1;$j<count($aDoc[$i]);$j++){
                          if($pad == $aDoc[$i][$j]['bestandsnaam'])
                              $dubbel++;
                          if($name == $aDoc[$i][$j]['naam'])
                              $dubbel++;
                     }
                  }
                  if($dubbel<3){
                      $cat = $this->getCategorieID($catNaam);
                      $this->_db->query("INSERT INTO documenten (naam, bestandsnaam, cat, eigenaar, datum) VALUES ('".$name."', '".$pad."', '".$cat."', '".$uid."', '".$datum."')");
                      return true;
                  }
                  else return false;
        }
        
        function delete($ID){
                  $this->_db->query("DELETE FROM documenten WHERE ID = ".$ID);
        }
        
        function getNaam($uid=false){
		if($uid===false)
			$uid=$this->_lid->getUid();
		$sNaamQuery="
			SELECT
				nickname, voornaam, tussenvoegsel, achternaam, geslacht, status
			FROM
				lid
			WHERE
				uid='$uid'
			LIMIT 1;";

		$rNaam=$this->_db->query($sNaamQuery);

		echo mysql_error();
		if($this->_db->numRows($rNaam)==1){
			$aNaam=$this->_db->next($rNaam);
			if($aNaam['status']=='S_NOVIET'){
				$sNaam='noviet '.$aNaam['voornaam'];
			}else{
				if($aNaam['geslacht']=='v'){
					$sNaam='ama. ';
				}else{
					$sNaam='am. ';
				}
				if($aNaam['tussenvoegsel']!=''){
					$sNaam.=$aNaam['tussenvoegsel'].' ';
				}
				$sNaam.=$aNaam['achternaam'];
			}
			return $sNaam;
		}else{
			return "";
		}
	}
}

?>
