<?php

# instellingen & rommeltjes
require_once('include.config.php');

require_once('class.commissie.php');
$cie=new Commissie($db, $lid);
$cie->loadCommissie('SocCie');


if(!$cie->magBewerken()){ header('location: http://csrdelft.nl'); }

echo '<h1>Overzicht saldi</h1><form action="/tools/saldo-overzicht.php" method="post">';
	
if(isset($_POST['namenRaw'])){
	$aUids=namen2uid($_POST['namenRaw'], $lid);
	if(is_array($aUids) AND count($aUids)!=0){
		echo '<table border="0">';
		echo '<tr><th>Naam</hd><th>SocCie</th><th>MaalCie</th></tr>';
		
		foreach($aUids as $aLid){
			if(isset($aLid['uid'])){
				//naam is gevonden en uniek, dus direct goed.
				$saldi=$lid->getSaldi($aLid['uid']);
				echo '<tr>';
				echo '<td ><input type="hidden" name="naam[]" value="'.$aLid['uid'].'" />'.$aLid['naam'].'</td>';
				echo '<td>'.sprintf('&euro; %01.2f', $saldi['soccie']).'</td><td>'.sprintf('&euro; %01.2f', $saldi['maalcie']).'</td></tr>';
			}else{
				//naam is niet duidelijk, geef ook een selectievakje met de mogelijke opties
				if(count($aLid['naamOpties'])>0){
					echo '<tr><td colspan="3"><select name="naam[]" class="tekst">';
					foreach($aLid['naamOpties'] as $aNaamOptie){
						echo '<option value="'.$aNaamOptie['uid'].'">'.$aNaamOptie['naam'].'</option>';
					}
					echo '</select></td>';
				}//dingen die niets opleveren wordt niets voor weergegeven.
			}
		}
		echo '</table>';
		
	}
}else{
	echo '<textarea name="namenRaw" rows="10" cols="40"></textarea>';
}

echo '<input type="submit" value="Verzenden" /></form>';


