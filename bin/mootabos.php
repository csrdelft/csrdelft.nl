#!/usr/bin/php5
<?php

# CLI Maaltijdbeheer C.S.R. Delft
# (c) 2006 PubCie C.S.R. Delft 
# 20-01-2006 Hans van Kranenburg

	session_id('maaltrack-cli');

	# instellingen & rommeltjes
	require_once('include.config.php');

	//Siri, Wouter K., Thomas Abrahamse
	$aGeenAbo=array('9101', '0016', '0401');
	for($moot=1;$moot<=4;$moot++){
		$aMootleden=array();	
		//kringvolgende leden van een moot ophalen
		$sMootleden="SELECT uid FROM lid WHERE moot=".$moot." AND kring !=0 AND status='S_NOVIET'";
		$rMootleden=$db->query($sMootleden);
		while($aMootledenData=$db->next($rMootleden)){
			$aMootleden[]=$aMootledenData['uid'];
		}
		
		//bestaande mootX abo's ophalen
		$sMootAbos="SELECT DISTINCT uid FROM maaltijdabo WHERE abosoort='A_MOOT".$moot."' OR abosoort='A_UBER".$moot."'";
		$rMootAbos=$db->query($sMootAbos);
		while($aMootAbosData=$db->next($rMootAbos)){
			$aMootAbos[]=$aMootAbosData['uid'];
		}
		
		//al bestaande mootX abo's van de lijst halen
		//$aAboInvoeren=array_diff($aMootleden, $aMootAbos);
		
		//niet abo leden eraf halen
		//$aAboInvoeren=array_diff($aMootleden, $aGeenAbo);
		//print_r($aAboInvoeren);
		
		//query's klussen
		foreach($aMootleden as $sUid){
			$sQuery="REPLACE INTO maaltijdabo (uid, abosoort )VALUES( '".$sUid."', 'A_MOOT".$moot."'), ('".$sUid."', 'A_UBER".$moot."');";
			echo $sQuery;
			if(false){//$db->query($sQuery)){
				echo "   ...OK\r\n";
			}else{
				echo "\r\n";
			}
		}
	}
	
	
?>
