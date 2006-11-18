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

	function WoonoordContent (&$woonoord, &$lid) {
		$this->_woonoord =& $woonoord;
		$this->_lid =& $lid;
	}
	function getTitel(){
		return 'Huizen, kotten en overige woonoorden';
	}
	function viewWaarbenik(){
		echo '<a href="/groepen/">Groepen</a> &raquo; '.$this->getTitel();
	}

	function view() {	
		echo 'Veel leden van C.S.R. wonen in verenigings-woonoorden. Als een woonoord aan
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
			echo '<tr><td width="50%" colspan="2"><h1>'.$titel.'</h1></td>
				<td width="2%">&nbsp;</td>
				<td width="47%" colspan="2"><h1>Bewoners</h1></td></tr>';
			
			foreach($woonoorden[$soort] as $woonoord) {
				$bBewerken=$this->_woonoord->magBewerken($woonoord['id']);
				echo '<tr height="30"><td>&nbsp;</td><td colspan="2" valign="middle"><a name="'.$woonoord['id'].'"></a>';
				if(trim($woonoord['link'])==''){ 
					echo '<h3>'.mb_htmlentities($woonoord['naam']).'</h3>'; 
				}else{ 
					echo '<h3><a href="'.htmlspecialchars($woonoord['link']).'">'.mb_htmlentities($woonoord['naam']).'</a></h3>'; 
				} 
				echo '('.htmlspecialchars($woonoord['adres']).')</td>';
				echo '<td rowspan="2">&nbsp;&nbsp;&nbsp;</td><td valign="top" rowspan="2">';
				foreach ($woonoord['bewoners'] as $bewoner) {
					if($this->_woonoord->isLid()) echo '<a href="/intern/profiel/'.$bewoner['uid'].'">';
					echo mb_htmlentities($bewoner['voornaam']).' ';
					if(trim($bewoner['tussenvoegsel'])!='') echo mb_htmlentities($bewoner['tussenvoegsel']).' ';
					echo mb_htmlentities($bewoner['achternaam']);
					if($this->_woonoord->isLid()) echo '</a>';
					if($bBewerken OR $this->_lid->hasPermission('P_LEDEN_MOD')){
						echo ' [ <a href="woonoorden.php?woonoordid='.$woonoord['id'].'&amp;uid='.$bewoner['uid'].'&amp;verwijderen"onclick=" return confirm(\'Weet u zeker dat u deze bewoner wilt verwijderen?\')">X</a> ]';
					}							
					echo "<br />\n";
				}
				echo '</td>';
				echo '</tr>';
				echo '<tr><td>&nbsp;&nbsp;&nbsp;</td><td valign="top">';
				if($woonoord['plaatje'] != '') echo '<img src="'.CSR_PICS.'/pagina/woonoorden/'.htmlspecialchars($woonoord['plaatje']).'" style="float: right;">'; 
				echo mb_htmlentities($woonoord['tekst']);
				if($bBewerken OR $this->_lid->hasPermission('P_LEDEN_MOD')){
					$bRawInvoer=false;
					//nieuw toevoeg formulier
					echo '<div class="quote"><form action="woonoorden.php?woonoordid='.$woonoord['id'].'#'.$woonoord['id'].'" method="post">';
					if(isset($_POST['rawBewoners']) AND trim($_POST['rawBewoners'])!='' AND isset($_GET['woonoordid']) AND $_GET['woonoordid']==$woonoord['id']){
						$aBewoners=namen2uid($_POST['rawBewoners'], $this->_lid);
						if(is_array($aBewoners)){
							foreach($aBewoners as $aBewoner){
								if(!isset($aBewoner['uid'])){
									//enkel dingen doen als het niet gelukt is, de rest is dan al ingevoerd.
									if(count($aBewoner['naamOpties'])>0){
										echo '<select name="bewoners[]" class="tekst">';
										foreach($aBewoner['naamOpties'] as $aNaamOptie){
											echo '<option value="'.$aNaamOptie['uid'].'">'.$aNaamOptie['naam'].'</option>';
										}
										echo '</select>';
									}
								}
							}
						}else{
							$bRawInvoer=true;
						}
					}else{
						$bRawInvoer=true;
					}
					if($bRawInvoer){					
						echo 'U kunt hier nieuwe bewoners voor uw huis invoeren, gescheiden door komma\'s:<br />';
						echo '<input type="text" length="60" value="" name="rawBewoners" class="tekst" />';
					}
					echo '<input type="submit" value="toevoegen" name="toevoegen" class="tekst" /></form></div>';
				}
				echo '</td><td>&nbsp;</td></tr>';
				echo '<tr><td colspan="3">&nbsp;</td></tr>';
			}
			
		}	
		echo '<tr><td><hr></td><td>&nbsp;</td><td><hr></td></tr>';
		echo '</table>';
	}
}

?>
