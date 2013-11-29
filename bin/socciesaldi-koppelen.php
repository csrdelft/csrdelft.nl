#!/usr/bin/php5-cgi
<?php



	session_id('maaltrack-cli');

	# instellingen & rommeltjes
	chdir('../lib/');
	require_once 'configuratie.include.php';
	

	$sLedenQuery="
		SELECT
			voornaam, achternaam, tussenvoegsel, uid, soccieID, createTerm
		FROM
			lid
		WHERE
			status='S_LID'
		OR
			status='S_GASTLID'
		OR
			status='S_NOVIET';";
		$rLeden=$db->query($sLedenQuery);
	while($aData=$db->next($rLeden)){
		$aLeden[]=$aData;
	}

	$soccieinput=simplexml_load_file ('../data/soccie.xml');
	$feutCount=0;

	$store = '';
	foreach($soccieinput as $soccielid){
		$sNaam=strtolower($soccielid->voornaam.' '.$soccielid->achternaam);
		foreach($aLeden as $aLid){
//			$sLidDbNaam=strtolower($aLid['voornaam'].($aLid['tussenvoegsel']=='' ? '' : ' '.$aLid['tussenvoegsel']).' '.$aLid['achternaam']);
			$sLidDbNaam=strtolower($aLid['voornaam'].' '.$aLid['achternaam']).($aLid['tussenvoegsel']=='' ? '' : ' '.$aLid['tussenvoegsel']);
			$uid=$aLid['uid'];
			$soccieID=$soccielid->id;
			$createTerm=$soccielid->createTerm;
/*			if($aLid['tussenvoegsel']!=''){
				$sLidDbNaam.=' '.$aLid['tussenvoegsel'];
			}*/
			//echo $sLidDbNaam;
			$gelijkheid=0; similar_text($sNaam, $sLidDbNaam, $gelijkheid);
			if($gelijkheid >88){
				//echo '  '.$sNaam.'('.$soccieID.') - '.$sLidDbNaam.'('.$uid.') << match';//."\r\n";

				//alleen updaten als er geen soccieID is gegeven
				if($aLid['soccieID'] == 0) {
					$query="UPDATE lid SET soccieID = ".$soccieID.", createTerm = '".$createTerm."' WHERE uid = '".$uid."';";
					$db->query($query);

					$store .= 'Opgeslagen: '.$sNaam.'('.$soccieID.' '.$createTerm.') - '.$sLidDbNaam.'('.$uid.') '."\r\n";
					$store .= $query . "\r\n";
				}

			}elseif($gelijkheid >80){
				$feutCount++;
				echo 'niet zeker: '.$sNaam.'('.$soccieID.' '.$createTerm.') - '.$sLidDbNaam.'('.$uid.') '."\r\n";
			}
		}

		reset($aLeden);

	}
echo "\nfeutcount: ".$feutCount."\n";

echo "\r\n \r\n".'De volgende leden zonder koppeling (soccieID=0) zijn geupdate naar:'."\r\n \r\n";
echo $store."\r\n";

