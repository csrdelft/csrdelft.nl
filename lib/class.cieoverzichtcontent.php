<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.cieoverzichtcontent.php
# -------------------------------------------------------------------
# Beeldt de overzichtspagina van de Commissies af
# -------------------------------------------------------------------


require_once ('class.simplehtml.php');
require_once ('class.commissie.php');

class CieOverzichtContent extends SimpleHTML {

	function CieOverzichtContent () {

	}
	function getTitel(){
		return 'Commissieoverzicht';
	}
	function viewWaarbenik(){
		echo '<a href="/groepen/">Groepen</a> &raquo; '.$this->getTitel();
	}
	function viewCieOverzicht(){
		$aCommissies=Commissie::getOverzicht();
		$lid=Lid::get_lid();
		foreach ($aCommissies as $cie) {
			echo '<div class="cie">';
			echo '<div class="cieleden">';
			$aCieLeden=Commissie::getLeden($cie['id']);
			if(is_array($aCieLeden)){
				foreach($aCieLeden as $aCieLid){
					echo $lid->getNaamLink($aCieLid['uid'], 'civitas', true, $aCieLid).'&nbsp;<em>'.$aCieLid['functie'].'</em><br />';
				}
			}else{
				echo $aCieLeden;
			}
			echo '</div>';
			
			echo '<h2><a href="/groepen/commissie/'.htmlspecialchars($cie['naam']).'.html">'.mb_htmlentities($cie['titel']).'</a></h2>';
				
			echo mb_htmlentities($cie['stekst']);
			echo '</div>';
		}
		//zo, nu nog even een clear zodat het niet buiten het witte contentvlak gaat vallen..
		echo '<div style="clear: both;"></div>';
		
	}//einde functie
	
	function view() {
		echo $this->viewCieOverzicht();
	}
}

?>
