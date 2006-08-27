<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.cieoverzichtcontent.php
# -------------------------------------------------------------------
#
# Beeldt de overzichtspagina van de Commissies af
#
# -------------------------------------------------------------------
# Historie:
# 15-09-2005 Hans van Kranenburg
# . gemaakt
# 18-10-2005 Jieter
# . leden en niet leden netter.

require_once ('class.simplehtml.php');
require_once ('bbcode/include.bbcode.php');
require_once ('class.commissie.php');

class CieOverzichtContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_commissie;
	var $_lid;

	### public ###

	function CieOverzichtContent (&$commissie, &$lid) {
		$this->_commissie =& $commissie;
		$this->_lid =& $lid;
	}
	function viewCieOverzicht(){
		if ($this->_lid->hasPermission('P_LEDEN_READ')){
			//met commissieleden
			echo '<table border="0" cellspacing="0" cellpadding="0" marginheight="0" marginwidth="0"><tr>';
			echo '<td><hr /><span class="kopje2">Commissies</span><hr /></td>
				<td width="2%"><img src="/images/pixel.gif" width="100%" height="1"></td>
				<td><hr /><span class="kopje2">Commissieleden</span><hr /></td>';
			echo '</tr>';
			$cieoverzicht = $this->_commissie->getOverzicht();
			foreach ($cieoverzicht as $cie) {
				echo '
					<tr height="30px">
						<td colspan="3" width="100%" valign="bottom">
							<a href="/informatie/commissie/'.htmlspecialchars($cie['naam']).'.html" class="a2">
								'.mb_htmlentities($cie['titel']).'
							</a>
						</td>
					</tr>
					<tr>
						<td width="49%" valign="top" >'.mb_htmlentities($cie['stekst']).'</td>
						<td width="2%"><img src="/images/pixel.gif" width="100%" height="1"></td>
						<td width="49%" valign="top">';
		
				$aCieLeden=$this->_commissie->getCieLeden($cie['id']);
				if(is_array($aCieLeden)){
					foreach($aCieLeden as $aCieLid){
						echo '<a href="../leden/profiel/'.$aCieLid['uid'].'">'.mb_htmlentities($aCieLid['naam']).'</a>&nbsp;<em>'.$aCieLid['functie'].'</em><br />';
					}
				}else{
					echo $aCieLeden;
				}
				echo '</td></tr>';
			}//einde foreach
			echo '</table>';
		}else{
			//zonder commissieleden
			echo '<table border="0" cellspacing="0" cellpadding="0" marginheight="0" marginwidth="0"><tr>';
			echo '<td><hr /><span class="kopje2">Commissies</span><hr /></td></tr>';
			$cieoverzicht = $this->_commissie->getOverzicht();
			foreach ($cieoverzicht as $cie) {
				echo '
					<tr height="30px">
						<td colspan="3" width="100%" valign="bottom">
							<a href="/informatie/commissie.php?cie='.htmlspecialchars($cie['naam']).'" class="a2">'.mb_htmlentities($cie['titel']).'</a>
					</td>
					</tr>
					<tr>
						<td valign="top" >'.mb_htmlentities($cie['stekst']).'</td>
					</tr>';	
			}
			echo ' </table>';
			
		}	
	}//einde functie
	
	function view() {
		echo $this->viewCieOverzicht();
	}
}

?>
