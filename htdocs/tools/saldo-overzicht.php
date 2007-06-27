<?php

# instellingen & rommeltjes
require_once('include.config.php');

if($lid->getUid()!='0436'){ header('location: http://csrdelft.nl'); }

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
				echo '<td colspan="3"><input type="hidden" name="naam[]" value="'.$aLid['uid'].'" />'.$aLid['naam'].'</td>';
				echo '<td>'.$saldi['soccie'].'</td><td>'.$saldi['maalcie'].'</td></tr>';
			}else{
				//naam is niet duidelijk, geef ook een selectievakje met de mogelijke opties
				if(count($aLid['naamOpties'])>0){
					echo '<tr><td><select name="naam[]" class="tekst">';
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
	echo '<input type="text" name="namenRaw" />';
}

echo '<input type="submit" value="Verzenden" /></form>';


