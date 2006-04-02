<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.commissiecontent.php
# -------------------------------------------------------------------
#
# Beeldt informatie af over Commissies
#
# -------------------------------------------------------------------
# Historie:
# 29-12-2004 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('bbcode/include.bbcode.php');
require_once ('class.commissie.php');

class CommissieContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_commissie;
	var $_lid;

	### public ###

	function CommissieContent (&$commissie, &$lid) {
		$this->_commissie =& $commissie;
		$this->_lid =& $lid;
	}
	
	function viewCommissie($cie){
		if($this->_lid->hasPermission('P_LEDEN_READ')){
			//met commissieleden
			echo '<table border="0" width="100%">
				<tr><td ><center><span class="kopje2">'.$cie['titel'].'</span></center></td><td width="250px">&nbsp;</td></tr>
				<tr><td>'.bbview($cie['tekst'], $cie['bbcode_uid']);
			//eventueel link
			if ($cie['link'] != '') {
				echo 'CommissieWebstek: <a href="'.htmlspecialchars($cie['link']).'">'.mb_htmlentities($cie['link']).'</a>';	
			}
			echo '</td><td valign="top">';
			$aCieLeden=$this->_commissie->getCieLeden($cie['id']);
			if(is_array($aCieLeden)){
				echo '<table border="0"  class="hoktable" ><tr><td colspan="2"><strong>Commissieleden:</strong></td></tr>';
				foreach($aCieLeden as $aCieLid){
					echo '<tr><td width="150px"><a href="../leden/profiel.php?uid='.$aCieLid['uid'].'">'.mb_htmlentities($aCieLid['naam']).'</a></td><td>'.mb_htmlentities($aCieLid['functie']).'</td></tr>';
				}
				echo '</table>';
			}else{
				if($aCieLeden!==false){
					echo '<table border="0" cellpadding="5px" class="hoktable" ><tr><td>'.$aCieLeden.'</td></tr></table>';
				}
			}
			echo '</td></tr></table><a href="javascript: history.go(-1)">[ Terug ]</a>';
		}else{
			//zonder commissieleden
			echo '<table border="0" width="100%">
				<tr><td ><center><span class="kopje2">'.$cie['titel'].'</span></center></td></tr>
				<tr><td>'.bbview($cie['tekst'], $cie['bbcode_uid']);
			//eventueel link
			if ($cie['link'] != '') {
				echo 'CommissieWebstek: <a href="'.htmlspecialchars($cie['link']).'">'.mb_htmlentities($cie['link']).'</a>';	
			}
			echo '</td></tr></table><a href="javascript: history.go(-1)">[ Terug ]</a>';
		}
		
	}
	function view() {
		$cie = $this->_commissie->getCommissie();
		echo $this->viewCommissie($cie);
	}
}

?>
