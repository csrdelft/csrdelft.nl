<?php

#
# C.S.R. Delft
#
# -------------------------------------------------------------------
# class.woonoordcontent.php
# -------------------------------------------------------------------
#
# Beeldt informatie af over Woonoorden
#
# -------------------------------------------------------------------
# Historie:
# 28-08-2005 Hans van Kranenburg
# . gemaakt
#

require_once ('class.simplehtml.php');
require_once ('bbcode/include.bbcode.php');
require_once ('class.woonoord.php');

class WoonoordContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_woonoord;
	var $_lid;

	var	$_soorten=array('W_HUIS' => 'C.S.R.-huizen', 'W_KOT' => 'C.S.R.-kotten', 'W_OVERIG' => 'Overige woonoorden');
	### public ###

	function WoonoordContent (&$woonoord) {
		$this->_woonoord =& $woonoord;
	}

	function view() {	
		echo '<center><span class="kopje2">Woonoorden</span></center><p>
			Veel leden van C.S.R. wonen in verenigings-woonoorden. Als een woonoord aan
			bepaalde eisen voldoet, kan het een offici&euml;le status als C.S.R. huis krijgen.
			Daarnaast zijn er kotten en overige woonoorden.<br /><br />
			Am. Talstra over C.S.R.-huizen in \'Veertig Roem, lustrumalmanak 2001\':<br />
			<em>"In 1990 werd de titel C.S.R.-huis" officieel ingevoerd, hoewel er natuurlijk 
			al veel langer verdiepingen of huizen bestonden die geheel of gedeeltelijk door C.S.R.-leden werden bewoond.';
		if ($this->_woonoord->isLid()){
			echo "Het net geopende en meest gewaardeerde huis Studenten Sanatorium Sonnenvanck had op 
				zijn openingsfeest in 1989 een certificaat ontvangen dat de benoeming tot C.S.R.-huis 
				vermeldde. De bewoners probeerden vervolgens door middel van een motie op de H.V. van 
				5 februari 1990 bescherming voor de titel C.S.R.-huis te regelen, en niet zonder succes.";
		}
		echo "Tijdens een extra H.V. enkele dagen later werden de voorwaarden vastgesteld: in een 
			C.S.R.-huis diende tenminste 75% van de minimaal drie bewoners lid van C.S.R. te zijn, 
			en een C.S.R.-kot bestond uit minimaal twee bewoners waarvan tenminste 50% C.S.R.-lid was.";
		if ($this->_woonoord->isLid()){
			echo 'Bovendien werd als specifieke eis gesteld dat de bewoners van kotten niet geabonneerd 
				mochten zijn op de Penthouse. ';
		}
		echo 'Erkende huizen nodigden het bestuur uit voor een maaltijd en ontvingen hierbij een 
			certificaat en een Kaapsviooltje. Bovendien waren zij verplicht een open activiteit voor 
			de hele vereniging te organiseren."</em><br /><br />';
		echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" marginheight="0" marginwidth="0">';
		$woonoorden = $this->_woonoord->getWoonoorden();
		foreach ($this->_soorten as $soort => $titel) {
			echo '<tr><td width="50%"><hr><span class="kopje2">'.$titel.'</span><hr></td>
				<td width="2%">&nbsp;</td>
				<td width="47%"><hr><span class="kopje2">Bewoners</span><hr></td></tr>';
			
			foreach($woonoorden[$soort] as $woonoord) {
				echo '<tr height="30"><td colspan="3" valign="middle">';
				if(trim($woonoord['link'])==''){ 
					echo '<span class="kopje3">'.mb_htmlentities($woonoord['naam']).'</span>'; 
				}else{ 
					echo '<a href="'.htmlspecialchars($woonoord['link']).'" class="a3">'.mb_htmlentities($woonoord['naam']).'</a>'; 
				} 
				echo '('.htmlspecialchars($woonoord['adres']).')</td>';
				echo '</tr>';
				echo '<tr><td valign="top">';
				if($woonoord['plaatje'] != '') echo '<img src="'.htmlspecialchars($woonoord['plaatje']).'" align="right">'; 
				echo mb_htmlentities($woonoord['tekst']).'</td>';
				echo '<td>&nbsp;</td><td valign="top">';
				foreach ($woonoord['bewoners'] as $bewoner) {
					if($this->_woonoord->isLid()) echo '<a href="/leden/profiel/'.$bewoner['uid'].'">';
					echo mb_htmlentities($bewoner['voornaam']).' ';
					if(trim($bewoner['tussenvoegsel'])!='') echo mb_htmlentities($bewoner['tussenvoegsel']).' ';
					echo mb_htmlentities($bewoner['achternaam']);
					if($this->_woonoord->isLid()) echo '</a>';
					echo "<br />\n";
				}
				echo '</td></tr>';
			}
		}	
		echo '<tr><td><hr></td><td>&nbsp;</td><td><hr></td></tr>';
		echo '</table>';
	}
}

?>
