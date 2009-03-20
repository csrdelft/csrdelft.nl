<?php

# instellingen & rommeltjes
require_once('include.config.php');

require_once('class.commissie.php');
$soccie=$maalcie=new Commissie($db, $lid);
$soccie->loadCommissie('SocCie');
$maalcie->loadCommissie('MaalCie');

if(!$soccie->magBewerken() OR !$maalcie->magbewerken()){ header('location: http://csrdelft.nl'); }

echo '<h1>Overzicht saldi</h1><form action="/tools/saldo-overzicht.php" method="post">';
	
if(isset($_POST['namenRaw'])){
	$aUids=namen2uid($_POST['namenRaw'], $lid);
	if(is_array($aUids) AND count($aUids)!=0){
		echo '<table border="0">';
		echo '<tr><th style="width: 300px;">Naam</hd><th style="width: 100px;">SocCie</th><th style="width: 100px;">MaalCie</th></tr>';
		
		foreach($aUids as $aLid){
			if(isset($aLid['uid'])){
				$lid=LidCache::getLid($aLid['uid']);
				//naam is gevonden en uniek, dus direct goed.
				$saldi=$lid->getSaldi(;
				echo '<tr>';
				echo '<td ><input type="hidden" name="naam[]" value="'.$aLid['uid'].'" />'.$aLid['naam'].'</td>';
				foreach(array('soccie', 'maalcie') as $cie){
					echo '<td style="text-align: right;';
					if($saldi[$cie]<0){ echo ' color: red;'; }
					echo '">'.sprintf('&euro; %01.2f', $saldi[$cie]).'</td>';
				}
				echo '</tr>';
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


