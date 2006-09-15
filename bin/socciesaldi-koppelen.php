#!/usr/bin/php5
<?php



	session_id('maaltrack-cli');

	# instellingen & rommeltjes
	require_once('/srv/www/www.csrdelft.nl/lib/include.config.php');
	require_once('include.common.php');

	# login-systeem
	require_once('class.lid.php');
	require_once('class.mysql.php');
	$db = new MySQL();
	$lid = new Lid($db);
	$sLedenQuery="
		SELECT
			voornaam, achternaam, tussenvoegsel, uid 
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
	foreach($soccieinput as $soccielid){
		$sNaam=strtolower($soccielid->voornaam.' '.$soccielid->achternaam);
		foreach($aLeden as $aLid){
			$sLidDbNaam=strtolower($aLid['voornaam'].' '.$aLid['achternaam']);
			$uid=$aLid['uid'];
			$soccieID=$soccielid->id;
			$createTerm=$soccielid->createTerm;
			if($aLid['tussenvoegsel']!=''){
				$sLidDbNaam.=' '.$aLid['tussenvoegsel'];
			}
			//echo $sLidDbNaam;
			$gelijkheid=0; similar_text($sNaam, $sLidDbNaam, $gelijkheid);
			if($gelijkheid >86){
				//echo '  '.$sNaam.'('.$soccieID.') - '.$sLidDbNaam.'('.$uid.') << match';//."\r\n";
				
				
				$query="REPLACE INTO socciesaldi ( uid, soccieID, createTerm )VALUES( '".$uid."', ".$soccieID.", '".$createTerm."');";
				$db->query($query);
				
				//echo $query."\r\n";
			}elseif($gelijkheid >80){	
				$feutCount++;
				echo 'niet zeker: '.$sNaam.'('.$soccieID.') - '.$sLidDbNaam.'('.$uid.') '."\r\n";
			}
		}
		
		reset($aLeden);

	}
echo "\nfeutcount: ".$feutCount."\n";
?>
