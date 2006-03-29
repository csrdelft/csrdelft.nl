<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.documentencontent.php
# -------------------------------------------------------------------
# Historie:
# 30-01-2006 Matthijs Neven
#

//require_once ('class.simplehtml.php');
//require_once ('class.lid.php');
//require_once ('class.documenten.php');

class DocumentenContent extends SimpleHTML {

	var $_documenten;
	var $_db;
	var $_lid;

	function DocumentenContent(&$documenten){
		$this->_documenten =& $documenten;
		$this->_lid =& $lid;
		$this->_db =& $db;
	}

	function view() {

                $aDocumenten=$this->_documenten->getDocumenten();
                $aCategorien=$this->_documenten->getCategorien();
                #$this->_documenten->add('Artikel uit arro over Bram Dingemanse',"Lezingen");

		echo '<center><span class="kopje2">Documenten</span><p></center>';
/*
		echo '<strong>toevoegen</strong>';
		echo '<br><br>bestand<br>
                <FORM METHOD="post" ACTION="/leden/documenten.php" ENCTYPE="multipart/form-data">
		<INPUT TYPE="file" NAME="bestand">
                <br><br>naam<br>
		<input type="text" name="naam" class="tekst" style="width:140px;" value="">
                <br><br>categorie<br>
                <SELECT name="categorie">';

                for($i=0;$i<count($aCategorien);$i++){
                  echo '<option>'.$aCategorien[$i]['naam'].'</option>';
                }

                echo '</select><br><br>
                <input type="submit" class="tekst" value=" toevoegen ">
                </FORM> ';
*/
                //require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
                
                echo '<table class="forumtabel">';

                for($i=1;$i<count($aDocumenten);$i++){
                  echo '<tr><td colspan="4" class="forumhoofd"><strong>'.$aDocumenten[$i][0].'</strong><br /></td></tr>';
                  for($j=1;$j<count($aDocumenten[$i]);$j++){

                      //uid omzetten in naam
                      $uid=$aDocumenten[$i][$j]['eigenaar'];
                      $sUsername=$this->_documenten->getNaam($uid);

                      //link maken
                      $slink='<a href="documenten/uploads/'.$aDocumenten[$i][$j]['bestandsnaam'].'">'.substr($aDocumenten[$i][$j]['bestandsnaam'],-3).'</a>';

                      //als meerdere dezelfde namen (verschillende bestandstypes) dan bijelkaar voegen
                      if($j+1<count($aDocumenten[$i]) && $aDocumenten[$i][$j]['naam']==$aDocumenten[$i][$j+1]['naam']){
                          while($j+1<count($aDocumenten[$i]) && $aDocumenten[$i][$j]['naam']==$aDocumenten[$i][$j+1]['naam']){
                              $j++;
                              $slink.='<a href="documenten/uploads/'.$aDocumenten[$i][$j]['bestandsnaam'].'" target="_blank"> '.substr($aDocumenten[$i][$j]['bestandsnaam'],-3).'</a>';
                          }
                      }
                      echo '<tr><td>'.$aDocumenten[$i][$j]['naam'].'</td><td>'.$slink.'</td><td>'.$sUsername.'</td><td>'.$aDocumenten[$i][$j]['datum'].'</td></tr>';
                   }
                   echo '<tr><td>&nbsp;</td></tr>';
                }
                echo '</table>';

       }
       

}

?>
