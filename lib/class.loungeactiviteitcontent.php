<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# class.sjaarsactiecontent.php
# -------------------------------------------------------------------


class LoungeactiviteitContent extends SimpleHTML {

	### private ###

	# de objecten die data leveren
	var $_loungeactiviteit;

	### public ###

	function LoungeactiviteitContent (&$loungeactiviteit) {
		$this->_loungeactiviteit =& $loungeactiviteit;
	}
	function getTitel(){
		return 'Mekellounge';
	}
	function viewWaarbenik(){
		echo '<a href="/intern/">Intern</a> &raquo; Mekellounge';
	}
	function viewNewActieForm(){
		$naam=$beschrijving='';
		$limiet=10;
		if(isset($_POST['actieNaam'])){ $naam=trim(htmlspecialchars($_POST['actieNaam'])); }
		if(isset($_POST['beschrijving'])){ $beschrijving=trim(htmlspecialchars($_POST['beschrijving'])); }
		if(isset($_POST['limiet'])){ $limiet=(int)$_POST['limiet']; }
		$sError=$this->_loungeactiviteit->getError();
		echo '<tr><td colspan="2">';
		echo '<h2 style="border-bottom: 1px dashed black; margin: 15px 0 0px 0;">Een activiteit toevoegen</h2></td></tr>';
		echo '<tr><td colspan="2">';

		echo '<form action="/intern/mekellounge.php" method="post"><div style="background-color: #F7F9FF; margin: 5px; padding: 5px; width: 710px;">';
		if(trim($sError)!=''){ echo '<div class="foutmelding">'.$sError.'</div>'; }
		echo '<strong>Naam:</strong> (van de activiteit)<br /><input type="text" value="'.$naam.'" name="actieNaam" class="tekst" style="width: 700px" /><br />';
		echo '<strong>Beschrijving:</strong><br /><textarea name="beschrijving" rows="8" class="tekst" style="width: 700px">'.$beschrijving.'</textarea><br />';
		echo '<strong>Limiet:</strong> (maximaal beschikbare plaatsen)<br /><input type="text" name="limiet" value="'.$limiet.'" class="tekst"  style="width: 700px" /><br />';
		echo '<input type="submit" value="verzenden" name="verzenden" />'; 

		echo '</div></form></td></tr>';
	}
	function view(){
		echo '<img src="'.CSR_PICS.'/kek/mekellounge.png" alt="Mekellounge" />
		<p>Het is bijna zover; de <strong>Mekellounge</strong> staat op het punt te beginnen: Drie middagen loungen in de TU-wijk. Het festijn vindt plaats op <strong>17, 18 en 19 april</strong>. De lounge is een rustpunt in de hectiek van alledag bij het studeren aan de universiteit. Het is een uitermate goed idee om te ontspannen onder het genot van een broodje of een gratis frisdrank en relaxte beats van loungemuziek.</p>
		<p>Dinsdagochtend gaan we een grote tent van 12 bij 6 meter opzetten, en de rest van de Lounge. Hier zijn dus 6 man voor nodig. De afbraak gaat meestal wat harder, dus we verwachten donderdag met 5 man toe te kunnen, om aan het eind van de middag en het begin van de avond alles weg te kunnen ruimen. Verder moet elke avond de tent bewaakt worden, dus vanaf het eind van de middag en de hele nacht moeten er minimaal 2 man aanwezig zijn. Geen probleem, want u kunt wat vriendjes uitnodigen om samen te pilzen, filmpje te doen en door te luisteren naar Lounge beats in een afgesloten tent.</p>
		<p>Verder gaat er elke dag koffie geschonken worden! Dit begint al iets eerder, zodat mensen de hele dag lekker gelokt worden. Daarom hebben we dit in twee groepen gezet; van 11:00 - 14:00 en van 14:00 - 16:00. Elke dag gaan we broodjes verkopen of aanbieden van 13:00 tot 16:00 en daar is Subway voor geregeld! Op woendag willen we graag een DJ; heeft u verstand van plaatjes of loungemuziek, of beide, schrijft u in!</p>
		<p>Check hieronder de <strong>inschrijflijst</strong> waarop u zich kan inschrijven en neem je <strong>vrienden en studiegenoten</strong> mee om <strong>gezamenlijk te loungen</strong>!</p>
		
		<h2>Inschrijflijst</h2>';
		$aLoungeactiviteiten=$this->_loungeactiviteit->getLoungeactiviteiten();
		
		echo '<table border="0" style="width: 100%">';
		echo '<tr><td><strong>Actie</strong></td><td style="width: 200px;  border-left: 1px solid black; padding: 0 0 0 10px;"><strong>Opgaven</strong></td></tr>';
		if(!is_array($aLoungeactiviteiten)){
			echo '<tr><td colspan="2">Nog geen loungeacties.</td></tr>';
		}else{
			foreach($aLoungeactiviteiten as $aLoungeactiviteit){
				$aSjaarsjes=$this->_loungeactiviteit->getAanmeldingen($aLoungeactiviteit['ID']);
				echo '<tr';
				if (is_array($aSjaarsjes) AND $aLoungeactiviteit['limiet']-count($aSjaarsjes)<1){
					echo ' style="color: #aaaaaa;"';
				}				
				echo '><td colspan="2">';
				echo '<h2 style="border-bottom: 1px dashed black; margin: 15px 0 0px 0;">'.mb_htmlentities($aLoungeactiviteit['actieNaam']).'</h2></td></tr>';
				echo '<tr';
				if (is_array($aSjaarsjes) AND $aLoungeactiviteit['limiet']-count($aSjaarsjes)<1){
					echo ' style="color: #aaaaaa;"';
				}
				echo '><td>';
				echo nl2br(mb_htmlentities($aLoungeactiviteit['beschrijving']));
				echo '</td><td style="vertical-align: top; border-left: 1px solid black; padding: 0 0 0 10px;">';
				$bAlAangemeld=false;
				if(is_array($aSjaarsjes) AND count($aSjaarsjes)!=0){
					$iAantal=count($aSjaarsjes);
					foreach($aSjaarsjes as $aSjaars){
						echo $aSjaars['naamLink'].'<br />';
						//controleren of het huidige lid hier al is aangemeld. dan bAlAangemeld zetten.
						if($aSjaars['uid']==$this->_loungeactiviteit->_lid->getUid()){ $bAlAangemeld=true; }
					}
					$limiet=$aLoungeactiviteit['limiet']-$iAantal;
				}else{
					echo 'Nog geen aanmeldingen.<br />';
					$limiet=$aLoungeactiviteit['limiet'];
				}
				if($limiet>=1){
					echo 'nog '.$limiet.' plaat'.($limiet==1 ? 's' : 'sen').' vrij.';
					if(!$bAlAangemeld){	
						echo '<br /><br /><a href="/intern/mekellounge.php?actieID='.$aLoungeactiviteit['ID'].'&amp;aanmelden" 
							onclick="'."return confirm('Weet u zeker dat u wilt aanmelden voor deze activiteit?')".'">aanmelden</a>';
					}
				}else{
					echo 'Deze activiteit is <strong>vol</strong>. U kunt zich niet meer aanmelden.';
				}
				echo '</td></tr>';
			}
		}//einde is_array($aLoungeactiviteit)
		//voor Berr en Jaep het formulier tonen...
		if($this->_loungeactiviteit->_lid->getUid()=='0622' OR $this->_loungeactiviteit->_lid->getUid()=='0308' OR $this->_loungeactiviteit->_lid->getUid()=='0304'){ $this->viewNewActieForm(); }
		echo '</table>';	
	}
}
	
