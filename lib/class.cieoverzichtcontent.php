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
		$ubb=new CsrUBB();
		
		//print een lijstje met linkjes naar commissies in deze pagina
		echo '<div id="cieLijstje">';
		echo '<ul style="float: left;">';
		$i=0;
		foreach($aCommissies as $cie){
			$i++;
			echo '<li style="list-style: none;"><a href="#cie'.$cie['id'].'">'.mb_htmlentities($cie['naam']).'</a></li>';
			if($i==ceil(count($aCommissies)*0.5)){ 
				echo '</ul><ul style=" clear: none;">'; 
			}
		}
		echo '</ul></div>';
		
		echo '<h2>Commissies bij C.S.R.</h2>
			<p class="persoonlijkverhaal">In de Civitas zijn er veel commissies actief om de vereniging 
			actief en actueel te houden. De OWeecommissie, afgekort OWeeCie is de eerste commissie van 
			C.S.R. waarmee een aankomende student in aanraking komt. Deze commissie zit vol met enthousiaste
			 mensen die verantwoordelijk zijn voor het duidelijk maken dat C.S.R. een briljante vereniging 
			is. Naast de OWeeCie kent C.S.R. ruim 20 commissies met ieder zijn eigen geweldige taak. 
			Commissies worden gevuld met leden die graag vaardigheden en kennis willen op doen van 
			commissiewerk. Wellicht klinkt dit erg groot en verplicht, maar bijvoorbeeld de OWeeCie is 
			ook een grote commissie. Als aspirant-lid bij C.S.R. wordt je ingedeeld in een sjaarscommissie 
			die een leuke activiteit/ding mag organiseren/maken en dan kom je er snel achter dat 
			commissiewerk gaaf is.<br />';
		echo '<br />Teun de Groot<br />Praeses der PubCie 2007-2008</p>';
		
		foreach ($aCommissies as $cie) {
			echo '<div class="cie" id="cie'.$cie['id'].'">';
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
				
			echo $ubb->getHtml($cie['stekst']);
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
