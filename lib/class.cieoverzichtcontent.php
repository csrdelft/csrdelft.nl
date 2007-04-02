<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.cieoverzichtcontent.php
# -------------------------------------------------------------------
# Beeldt de overzichtspagina van de Commissies af
# -------------------------------------------------------------------


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
	function getTitel(){
		return 'Commissieoverzicht';
	}
	function viewWaarbenik(){
		echo '<a href="/groepen/">Groepen</a> &raquo; '.$this->getTitel();
	}
	function viewCieOverzicht(){
		$aCommissies=$this->_commissie->getOverzicht();
		
		echo '<table>';
		foreach ($aCommissies as $cie) {
			echo '
				<tr height="30px">
					<td colspan="3" width="100%" valign="bottom">
						<h2>
							<a href="./commissie/'.htmlspecialchars($cie['naam']).'.html">'.mb_htmlentities($cie['titel']).'</a>
						</h2>
					</td>
				</tr>
				<tr>
					<td width="49%" valign="top" >'.mb_htmlentities($cie['stekst']).'</td>
					<td width="2%"><img src="/images/pixel.gif" width="100%" height="1"></td>
					<td width="49%" valign="top">';
	
			$aCieLeden=$this->_commissie->getCieLeden($cie['id']);
			if(is_array($aCieLeden)){
				foreach($aCieLeden as $aCieLid){
					echo $this->_lid->getNaamLink($aCieLid['uid'], 'civitas', true, $aCieLid).'&nbsp;<em>'.$aCieLid['functie'].'</em><br />';
				}
			}else{
				echo $aCieLeden;
			}
			echo '</td></tr>';
		}//einde foreach
		echo '</table>';
		
	}//einde functie
	
	function view() {
		echo $this->viewCieOverzicht();
	}
}

?>
